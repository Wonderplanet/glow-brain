using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Calculator;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public class HomeViewModelTranslator
    {
        public HomeHeaderViewModel GenerateHomeHeaderViewModel(
            HomeUserParameterUseCaseModel parameter,
            UserProfileModel profile,
            HomeHeaderIconUseCaseModel iconModel)
        {
            return new HomeHeaderViewModel(
                profile.Name,
                UserAvatarPath.FromUnitAssetKey(iconModel.UnitAssetKey),
                new UserAvatarFramePath(AvatarAssetPath.GetAvatarFrameIconPath(iconModel.EmblemAssetKey == null ? "" : iconModel.EmblemAssetKey.Value)),
                EmblemIconAssetPath.FromAssetKey(iconModel.EmblemAssetKey),
                parameter.Level,
                parameter.Exp,
                parameter.NextExp,
                parameter.Stamina,
                parameter.MaxStamina,
                parameter.Coin,
                parameter.FreeDiamond,
                parameter.PaidDiamond);
        }

        public HomeHeaderStaminaViewModel GenerateHomeHeaderStaminaViewModel(HomeUserStaminaUseCaseModel model)
        {
            return new HomeHeaderStaminaViewModel(
                model.Stamina,
                model.MaxStamina,
                model.RemainFullRecoverySeconds,
                model.RemainUpdatingStaminaRecoverSecond,
                model.IsHeldAdditionalStaminaPassEffect);
        }
    }
}
