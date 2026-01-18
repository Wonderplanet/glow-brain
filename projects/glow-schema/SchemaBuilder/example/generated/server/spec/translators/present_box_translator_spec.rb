require 'rails_helper'

RSpec.describe "PresentBoxTranslator" do
  subject { PresentBoxTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, message: 1, prize: 2, sent_at: 3, expire_at: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(PresentBoxViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.message).to eq use_case_data.message
      expect(view_model.prize).to eq use_case_data.prize
      expect(view_model.sent_at).to eq use_case_data.sent_at
      expect(view_model.expire_at).to eq use_case_data.expire_at
    end
  end
end
