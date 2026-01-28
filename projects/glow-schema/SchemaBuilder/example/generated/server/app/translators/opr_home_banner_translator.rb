class OprHomeBannerTranslator
  def self.translate(opr_home_banner_model)
    view_model = OprHomeBannerViewModel.new
    view_model.id = opr_home_banner_model.id
    view_model.destination = opr_home_banner_model.destination
    view_model.destination_id = opr_home_banner_model.destination_id
    view_model.banner_path = opr_home_banner_model.banner_path
    view_model.start_at = opr_home_banner_model.start_at
    view_model.end_at = opr_home_banner_model.end_at
    view_model.sort_number = opr_home_banner_model.sort_number
    view_model
  end
end
