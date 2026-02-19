require 'rails_helper'

RSpec.describe "NormalMissionTranslator" do
  subject { NormalMissionTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mission_number: 0, counter: 1, received_level: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(NormalMissionViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mission_number).to eq use_case_data.mission_number
      expect(view_model.counter).to eq use_case_data.counter
      expect(view_model.received_level).to eq use_case_data.received_level
    end
  end
end
