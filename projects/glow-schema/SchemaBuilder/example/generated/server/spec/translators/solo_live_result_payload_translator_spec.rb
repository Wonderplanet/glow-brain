require 'rails_helper'

RSpec.describe "SoloLiveResultPayloadTranslator" do
  subject { SoloLiveResultPayloadTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, turn_scores: 0, after_point_up_score: 1, check_hash: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveResultPayloadViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.turn_scores).to eq use_case_data.turn_scores
      expect(view_model.after_point_up_score).to eq use_case_data.after_point_up_score
      expect(view_model.check_hash).to eq use_case_data.check_hash
    end
  end
end
