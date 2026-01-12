class HomeBadgeTranslator
  def self.translate(home_badge_model)
    view_model = HomeBadgeViewModel.new
    view_model.present_box_count = home_badge_model.present_box_count
    view_model
  end
end
