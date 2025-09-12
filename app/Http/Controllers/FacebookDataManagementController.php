<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostFacebookFanpageNotAds;
use App\Models\FacebookFanpage;

class FacebookDataManagementController extends Controller
{
    public function pages(Request $request)
    {
        $filters = $request->only(['page_id', 'date_from', 'date_to', 'post_types', 'search', 'quick_search', 'sort_by']);
        
        // Get pages for dropdown
        $pages = FacebookFanpage::orderBy('name')->get();
        
        // Get selected page
        $selected_page = null;
        if (!empty($filters['page_id'])) {
            $selected_page = $pages->firstWhere('id', $filters['page_id']);
        }
        
        if ($selected_page) {
            // Build query
            $postsQuery = PostFacebookFanpageNotAds::where('page_id', $selected_page->id);
            
            // Apply filters
            if (!empty($filters['date_from'])) {
                $postsQuery->whereDate('created_time', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $postsQuery->whereDate('created_time', '<=', $filters['date_to']);
            }
            
            // Post types filter (multi-select)
            if (!empty($filters['post_types']) && is_array($filters['post_types'])) {
                $postsQuery->where(function($q) use ($filters) {
                    foreach ($filters['post_types'] as $postType) {
                        if ($postType === 'photo') {
                            $q->orWhere(function($q2) {
                                $q2->where('type', 'photo')
                                   ->orWhere('attachments', 'like', '%"media_type":"photo"%')
                                   ->orWhere('attachments', 'like', '%"media_type":"album"%');
                            });
                        } elseif ($postType === 'video') {
                            $q->orWhere(function($q2) {
                                $q2->where('type', 'video')
                                   ->orWhere('attachments', 'like', '%"media_type":"video"%');
                            });
                        } elseif ($postType === 'link') {
                            $q->orWhere(function($q2) {
                                $q2->where('type', 'link')
                                   ->orWhere('attachments', 'like', '%"media_type":"link"%');
                            });
                        } elseif ($postType === 'album') {
                            $q->orWhere('attachments', 'like', '%"media_type":"album"%');
                        } elseif ($postType === 'status') {
                            $q->orWhere(function($q2) {
                                $q2->where('type', 'status')
                                   ->orWhere(function($q3) {
                                       $q3->whereNull('attachments')
                                          ->orWhere('attachments', '=', '[]')
                                          ->orWhere('attachments', '=', 'null');
                                   });
                            });
                        }
                    }
                });
            }
            
            // Search filter
            $searchTerm = $filters['search'] ?? $filters['quick_search'] ?? '';
            if (!empty($searchTerm)) {
                $postsQuery->where(function($q) use ($searchTerm) {
                    $q->where('message', 'like', '%'.$searchTerm.'%')
                      ->orWhere('name', 'like', '%'.$searchTerm.'%')
                      ->orWhere('description', 'like', '%'.$searchTerm.'%');
                });
            }
            
            // Apply sorting
            $sortBy = $filters['sort_by'] ?? 'created_time_desc';
            switch ($sortBy) {
                case 'created_time_asc':
                    $postsQuery->orderBy('created_time', 'asc');
                    break;
                case 'post_impressions_desc':
                    $postsQuery->orderBy('post_impressions', 'desc');
                    break;
                case 'post_impressions_asc':
                    $postsQuery->orderBy('post_impressions', 'asc');
                    break;
                case 'post_reactions_desc':
                    $postsQuery->orderBy('post_reactions', 'desc');
                    break;
                case 'post_reactions_asc':
                    $postsQuery->orderBy('post_reactions', 'asc');
                    break;
                case 'post_comments_desc':
                    $postsQuery->orderBy('post_comments', 'desc');
                    break;
                case 'post_comments_asc':
                    $postsQuery->orderBy('post_comments', 'asc');
                    break;
                case 'post_shares_desc':
                    $postsQuery->orderBy('post_shares', 'desc');
                    break;
                case 'post_shares_asc':
                    $postsQuery->orderBy('post_shares', 'asc');
                    break;
                default: // created_time_desc
                    $postsQuery->orderBy('created_time', 'desc');
                    break;
            }
            
            // Paginate posts
            $paginatedPosts = $postsQuery->paginate(10)->appends($request->query());
        } else {
            $paginatedPosts = collect();
        }
        
        // If AJAX request, return only the posts container
        if ($request->ajax()) {
            return view('facebook.data-management.partials.posts-container', compact('paginatedPosts', 'selected_page', 'filters'));
        }
        
        // Regular request, return full page
        return view('facebook.data-management.pages', compact('pages', 'selected_page', 'paginatedPosts', 'filters'));
    }
} 