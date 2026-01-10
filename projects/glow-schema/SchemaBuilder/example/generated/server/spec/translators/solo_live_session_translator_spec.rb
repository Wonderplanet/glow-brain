require 'rails_helper'

RSpec.describe "SoloLiveSessionTranslator" do
  subject { SoloLiveSessionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_solo_live_id: 0, puzzle: 1, status: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveSessionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_solo_live_id).to eq use_case_data.opr_solo_live_id
      expect(view_model.puzzle).to eq use_case_data.puzzle
      expect(view_model.status).to eq use_case_data.status
    end
  end
end
