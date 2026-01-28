class DayQuestTranslator
  def self.translate(day_quest_model)
    view_model = DayQuestViewModel.new
    view_model.id = day_quest_model.id
    view_model.mst_day_quest_id = day_quest_model.mst_day_quest_id
    view_model.play_count = day_quest_model.play_count
    view_model.clear_count = day_quest_model.clear_count
    view_model
  end
end
