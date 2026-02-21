class GachaResultTranslator
  def self.translate(gacha_result_model)
    view_model = GachaResultViewModel.new
    view_model.received = gacha_result_model.received
    view_model.received_histories = gacha_result_model.received_histories
    view_model.gacha = gacha_result_model.gacha
    view_model.gacha_sale_history = gacha_result_model.gacha_sale_history
    view_model.gacha_oha_history = gacha_result_model.gacha_oha_history
    view_model.updated_mission = gacha_result_model.updated_mission
    view_model.updated_solo_stories = gacha_result_model.updated_solo_stories
    view_model
  end
end
