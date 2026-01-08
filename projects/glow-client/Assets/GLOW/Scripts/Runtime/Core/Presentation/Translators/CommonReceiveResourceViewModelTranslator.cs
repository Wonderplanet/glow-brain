using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Core.Presentation.Translators
{
    public static class CommonReceiveResourceViewModelTranslator
    {
        public static CommonReceiveResourceViewModel TranslateToCommonReceiveViewModel(
            CommonReceiveResourceModel resourceModel,
            bool isShowRewardBadge = false)
        {
            var preConversion = PlayerResourceIconViewModel.Empty;
            if (!resourceModel.PreConversionPlayerResourceModel.IsEmpty())
            {
                preConversion = PlayerResourceIconViewModelTranslator
                    .ToPlayerResourceIconViewModel(
                        resourceModel.PreConversionPlayerResourceModel, isShowRewardBadge);
            }

            return new CommonReceiveResourceViewModel(
                resourceModel.UnreceivedRewardReasonType,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(resourceModel.PlayerResourceModel, isShowRewardBadge),
                preConversion);
        }
    }
}
