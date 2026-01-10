require 'rails_helper'

RSpec.describe "UserApTranslator" do
  subject { UserApTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, base_ap: 0, below_max_at: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserApViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.base_ap).to eq use_case_data.base_ap
      expect(view_model.below_max_at).to eq use_case_data.below_max_at
    end
  end
end
