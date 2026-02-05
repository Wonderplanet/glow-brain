require 'rails_helper'

RSpec.describe "RecoverApResultTranslator" do
  subject { RecoverApResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_ap: 0, crystal: 1, item: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(RecoverApResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_ap).to eq use_case_data.user_ap
      expect(view_model.crystal).to eq use_case_data.crystal
      expect(view_model.item).to eq use_case_data.item
    end
  end
end
