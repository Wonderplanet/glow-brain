class SoloLiveRankingTranslator
  def self.translate(solo_live_ranking_model)
    view_model = SoloLiveRankingViewModel.new
    view_model.profiles = solo_live_ranking_model.profiles
    view_model.total_count = solo_live_ranking_model.total_count
    view_model
  end
end
