class OprMainStoryReadCampaignTranslator
  def self.translate(opr_main_story_read_campaign_model)
    view_model = OprMainStoryReadCampaignViewModel.new
    view_model.id = opr_main_story_read_campaign_model.id
    view_model.mst_main_story_chapter_id = opr_main_story_read_campaign_model.mst_main_story_chapter_id
    view_model.start_at = opr_main_story_read_campaign_model.start_at
    view_model.end_at = opr_main_story_read_campaign_model.end_at
    view_model
  end
end
