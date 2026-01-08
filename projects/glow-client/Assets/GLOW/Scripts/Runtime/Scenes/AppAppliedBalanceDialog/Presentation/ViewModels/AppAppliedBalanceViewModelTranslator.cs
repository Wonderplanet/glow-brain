using GLOW.Scenes.AppAppliedBalanceDialog.Domain;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Presentation
{
    public class AppAppliedBalanceViewModelTranslator
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public AppAppliedBalanceDialogViewModel TranslateViewModel(AppAppliedBalanceUseCaseModel model)
        {
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            return new AppAppliedBalanceDialogViewModel(
                model.UserParameterModel.FreeDiamond,
                model.UserParameterModel.GetPaidDiamondFromPlatform(platformId),
                model.UserParameterModel.GetTotalDiamond(platformId));
        }
    }
}
