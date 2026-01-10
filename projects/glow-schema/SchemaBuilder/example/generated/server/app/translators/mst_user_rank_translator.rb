class MstUserRankTranslator
  def self.translate(mst_user_rank_model)
    view_model = MstUserRankViewModel.new
    view_model.id = mst_user_rank_model.id
    view_model.rank_level = mst_user_rank_model.rank_level
    view_model.required_point = mst_user_rank_model.required_point
    view_model.max_ap = mst_user_rank_model.max_ap
    view_model.max_friend_count = mst_user_rank_model.max_friend_count
    view_model
  end
end
