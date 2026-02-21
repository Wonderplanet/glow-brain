class OprPointUpCharacterVariantTranslator
  def self.translate(opr_point_up_character_variant_model)
    view_model = OprPointUpCharacterVariantViewModel.new
    view_model.opr_event_id = opr_point_up_character_variant_model.opr_event_id
    view_model.mst_character_variant_id = opr_point_up_character_variant_model.mst_character_variant_id
    view_model.limit_break_count0_percentage = opr_point_up_character_variant_model.limit_break_count0_percentage
    view_model.limit_break_count1_percentage = opr_point_up_character_variant_model.limit_break_count1_percentage
    view_model.limit_break_count2_percentage = opr_point_up_character_variant_model.limit_break_count2_percentage
    view_model.limit_break_count3_percentage = opr_point_up_character_variant_model.limit_break_count3_percentage
    view_model
  end
end
