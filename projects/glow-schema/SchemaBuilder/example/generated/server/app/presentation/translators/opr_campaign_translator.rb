class OprCampaignTranslator
  def self.translate(opr_campaign_model)
    view_model = OprCampaignViewModel.new
    view_model.id = opr_campaign_model.id
    view_model.target = opr_campaign_model.target
    view_model.feature = opr_campaign_model.feature
    view_model.percentage = opr_campaign_model.percentage
    view_model.min_rank = opr_campaign_model.min_rank
    view_model.max_rank = opr_campaign_model.max_rank
    view_model.start_at = opr_campaign_model.start_at
    view_model.end_at = opr_campaign_model.end_at
    view_model
  end
end
