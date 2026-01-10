require 'rails_helper'

RSpec.describe "OprTutorialGachaTranslator" do
  subject { OprTutorialGachaTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, caution: 2, secondary_ssr_bonus: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprTutorialGachaViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.caution).to eq use_case_data.caution
      expect(view_model.secondary_ssr_bonus).to eq use_case_data.secondary_ssr_bonus
    end
  end
end
