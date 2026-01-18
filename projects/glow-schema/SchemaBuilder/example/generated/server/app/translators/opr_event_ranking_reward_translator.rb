class OprEventRankingRewardTranslator
  def self.translate(opr_event_ranking_reward_model)
    view_model = OprEventRankingRewardViewModel.new
    view_model.opr_event_id = opr_event_ranking_reward_model.opr_event_id
    view_model.prize = opr_event_ranking_reward_model.prize
    view_model
  end
end
