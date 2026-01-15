class EventNormalQuestTranslator
  def self.translate(event_normal_quest_model)
    view_model = EventNormalQuestViewModel.new
    view_model.id = event_normal_quest_model.id
    view_model.opr_event_normal_quest_id = event_normal_quest_model.opr_event_normal_quest_id
    view_model.play_count = event_normal_quest_model.play_count
    view_model.clear_count = event_normal_quest_model.clear_count
    view_model
  end
end
