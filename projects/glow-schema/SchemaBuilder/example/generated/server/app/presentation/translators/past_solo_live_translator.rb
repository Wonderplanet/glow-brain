class PastSoloLiveTranslator
  def self.translate(past_solo_live_model)
    view_model = PastSoloLiveViewModel.new
    view_model.opr_solo_live = past_solo_live_model.opr_solo_live
    view_model.joined = past_solo_live_model.joined
    view_model
  end
end
