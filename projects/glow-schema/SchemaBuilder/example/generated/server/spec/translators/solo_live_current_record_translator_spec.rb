require 'rails_helper'

RSpec.describe "SoloLiveCurrentRecordTranslator" do
  subject { SoloLiveCurrentRecordTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_solo_live_id: 0, user_id: 1, score: 2, achieved_at: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveCurrentRecordViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_solo_live_id).to eq use_case_data.opr_solo_live_id
      expect(view_model.user_id).to eq use_case_data.user_id
      expect(view_model.score).to eq use_case_data.score
      expect(view_model.achieved_at).to eq use_case_data.achieved_at
    end
  end
end
