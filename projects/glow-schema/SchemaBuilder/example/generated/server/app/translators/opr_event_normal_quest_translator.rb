class OprEventNormalQuestTranslator
  def self.translate(opr_event_normal_quest_model)
    view_model = OprEventNormalQuestViewModel.new
    view_model.id = opr_event_normal_quest_model.id
    view_model.opr_event_id = opr_event_normal_quest_model.opr_event_id
    view_model.guerrilla_percentage = opr_event_normal_quest_model.guerrilla_percentage
    view_model.name = opr_event_normal_quest_model.name
    view_model.stamina_consumption = opr_event_normal_quest_model.stamina_consumption
    view_model.recommended_level = opr_event_normal_quest_model.recommended_level
    view_model.opr_event_normal_quest_puzzle_stage_id = opr_event_normal_quest_model.opr_event_normal_quest_puzzle_stage_id
    view_model.dependency_opr_event_normal_quest_id = opr_event_normal_quest_model.dependency_opr_event_normal_quest_id
    view_model.prize = opr_event_normal_quest_model.prize
    view_model
  end
end
