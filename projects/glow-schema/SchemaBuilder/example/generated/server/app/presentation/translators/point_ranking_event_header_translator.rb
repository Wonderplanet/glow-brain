class PointRankingEventHeaderTranslator
  def self.translate(point_ranking_event_header_model)
    view_model = PointRankingEventHeaderViewModel.new
    view_model.point = point_ranking_event_header_model.point
    view_model.rank = point_ranking_event_header_model.rank
    view_model
  end
end
