require 'rails_helper'

RSpec.describe "PastSoloLiveTranslator" do
  subject { PastSoloLiveTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_solo_live: 0, joined: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(PastSoloLiveViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_solo_live).to eq use_case_data.opr_solo_live
      expect(view_model.joined).to eq use_case_data.joined
    end
  end
end
