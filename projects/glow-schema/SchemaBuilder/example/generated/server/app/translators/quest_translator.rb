class QuestTranslator
  def self.translate(quest_model)
    view_model = QuestViewModel.new
    view_model.id = quest_model.id
    view_model.mst_quest_id = quest_model.mst_quest_id
    view_model.play_count = quest_model.play_count
    view_model.clear_count = quest_model.clear_count
    view_model
  end
end
