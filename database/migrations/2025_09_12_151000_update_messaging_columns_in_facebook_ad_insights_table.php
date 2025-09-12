<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // 1) Drop các cột messaging thừa (nếu tồn tại)
            foreach ([
                'messaging_conversations_started',
                'messaging_conversations_started_by_click',
                'messaging_conversations_started_by_reply',
                'messaging_conversations_started_by_other',
            ] as $col) {
                if (Schema::hasColumn('facebook_ad_insights', $col)) {
                    $table->dropColumn($col);
                }
            }

            // 2) Thêm các cột messaging và hành vi phổ biến từ actions (unsigned integer, default 0)
            $addInt = function(string $name) use ($table) {
                if (!Schema::hasColumn('facebook_ad_insights', $name)) {
                    $table->unsignedInteger($name)->default(0)->after('actions');
                }
            };

            // Messaging core
            $addInt('messaging_conversation_started_7d');
            $addInt('total_messaging_connection');
            $addInt('messaging_conversation_replied_7d');
            $addInt('messaging_welcome_message_view');
            $addInt('messaging_first_reply');
            $addInt('messaging_user_depth_2_message_send');
            $addInt('messaging_user_depth_3_message_send');
            $addInt('messaging_user_depth_5_message_send');
            $addInt('messaging_block');

            // Engagement/common actions
            $addInt('post_engagement');
            $addInt('page_engagement');
            $addInt('post_interaction_gross');
            $addInt('post_reaction');
            $addInt('link_click');

            // Leads & checkout (giữ riêng để có thể query nhanh)
            $addInt('lead');
            $addInt('onsite_conversion_lead');
            $addInt('onsite_web_lead');
            $addInt('lead_grouped');
            $addInt('offsite_complete_registration_add_meta_leads');
            $addInt('offsite_search_add_meta_leads');
            $addInt('offsite_content_view_add_meta_leads');

            $addInt('onsite_conversion_initiate_checkout');
            $addInt('onsite_web_initiate_checkout');
            $addInt('omni_initiated_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Drop các cột mới
            $dropCols = [
                'messaging_conversation_started_7d','total_messaging_connection','messaging_conversation_replied_7d','messaging_welcome_message_view',
                'messaging_first_reply','messaging_user_depth_2_message_send','messaging_user_depth_3_message_send','messaging_user_depth_5_message_send','messaging_block',
                'post_engagement','page_engagement','post_interaction_gross','post_reaction','link_click',
                'lead','onsite_conversion_lead','onsite_web_lead','lead_grouped','offsite_complete_registration_add_meta_leads','offsite_search_add_meta_leads','offsite_content_view_add_meta_leads',
                'onsite_conversion_initiate_checkout','onsite_web_initiate_checkout','omni_initiated_checkout',
            ];
            foreach ($dropCols as $col) {
                if (Schema::hasColumn('facebook_ad_insights', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Re-add các cột cũ để rollback an toàn
            foreach ([
                'messaging_conversations_started',
                'messaging_conversations_started_by_click',
                'messaging_conversations_started_by_reply',
                'messaging_conversations_started_by_other',
            ] as $col) {
                if (!Schema::hasColumn('facebook_ad_insights', $col)) {
                    $table->unsignedInteger($col)->default(0);
                }
            }
        });
    }
};


