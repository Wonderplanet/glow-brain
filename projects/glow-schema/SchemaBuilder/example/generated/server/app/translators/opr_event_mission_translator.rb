class OprEventMissionTranslator
  def self.translate(opr_event_mission_model)
    view_model = OprEventMissionViewModel.new
    view_model.id = opr_event_mission_model.id
    view_model.opr_event_id = opr_event_mission_model.opr_event_id
    view_model.mission_number = opr_event_mission_model.mission_number
    view_model.category = opr_event_mission_model.category
    view_model.description = opr_event_mission_model.description
    view_model.level = opr_event_mission_model.level
    view_model.goal_count = opr_event_mission_model.goal_count
    view_model.opr_event_normal_quest_id = opr_event_mission_model.opr_event_normal_quest_id
    view_model.prize = opr_event_mission_model.prize
    view_model
  end
end
