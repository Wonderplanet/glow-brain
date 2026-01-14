class MainStoryReadCampaignRewardTranslator
  def self.translate(main_story_read_campaign_reward_model)
    view_model = MainStoryReadCampaignRewardViewModel.new
    view_model.opr_main_story_read_campaign_reward_id = main_story_read_campaign_reward_model.opr_main_story_read_campaign_reward_id
    view_model
  end
end
