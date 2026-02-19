class SoloLiveRankingRewardTranslator
  def self.translate(solo_live_ranking_reward_model)
    view_model = SoloLiveRankingRewardViewModel.new
    view_model.rank_from = solo_live_ranking_reward_model.rank_from
    view_model.rank_to = solo_live_ranking_reward_model.rank_to
    view_model.border = solo_live_ranking_reward_model.border
    view_model.is_lucky_number = solo_live_ranking_reward_model.is_lucky_number
    view_model.prizes = solo_live_ranking_reward_model.prizes
    view_model
  end
end
