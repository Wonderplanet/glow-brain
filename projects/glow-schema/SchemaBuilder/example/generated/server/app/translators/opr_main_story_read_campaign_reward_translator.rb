class OprMainStoryReadCampaignRewardTranslator
  def self.translate(opr_main_story_read_campaign_reward_model)
    view_model = OprMainStoryReadCampaignRewardViewModel.new
    view_model.id = opr_main_story_read_campaign_reward_model.id
    view_model.opr_main_story_read_campaign_id = opr_main_story_read_campaign_reward_model.opr_main_story_read_campaign_id
    view_model.episode_number = opr_main_story_read_campaign_reward_model.episode_number
    view_model.crystal = opr_main_story_read_campaign_reward_model.crystal
    view_model
  end
end
