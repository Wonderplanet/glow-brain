using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain.Factory
{
    public class UserStaminaModelFactory : IUserStaminaModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUserLevelDataRepository MstUserLevelDataRepository { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        UserStaminaModel IUserStaminaModelFactory.Create()
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            var userParameterModel = gameFetchModel.UserParameterModel;
            var maxStamina = MstUserLevelDataRepository.GetUserLevelModel(userParameterModel.Level).MaxStamina;
            var passEffect = HeldPassEffectRepository.GetHeldPassEffectListModel().
                GetPassEffectValue(
                    ShopPassEffectType.StaminaAddRecoveryLimit,
                    TimeProvider.Now);

            var additionalStaminaLimit = passEffect.ToStamina();
            maxStamina += additionalStaminaLimit;

            var recoveryStaminaMinutes = MstConfigRepository.GetConfig(MstConfigKey.RecoveryStaminaMinute)
                .Value.ToRecoveryStaminaMinutes();


            var currentStaminaResult = StaminaCalculator.CalcStaminaInfo(
                userParameterModel.StaminaUpdatedAt,
                TimeProvider.Now,
                userParameterModel.CurrentStamina,
                maxStamina,
                recoveryStaminaMinutes);

            var remainFullRecoverySeconds = StaminaCalculator.CalcStaminaFullRecoverySecond(
                userParameterModel.StaminaUpdatedAt,
                TimeProvider.Now,
                userParameterModel.CurrentStamina,
                maxStamina,
                recoveryStaminaMinutes);

            var maxDailyBuyStaminaAdCount = MstConfigRepository
                .GetConfig(MstConfigKey.MaxDailyBuyStaminaAdCount).Value
                .ToBuyStaminaAdCount();


            var dailyBuyStaminaAdIntervalMinutes = MstConfigRepository
                .GetConfig(MstConfigKey.DailyBuyStaminaAdIntervalMinutes).Value
                .ToRecoveryStaminaMinutes();

            var canRecoverByAd = StaminaRecoverCalculator.CalcIsAdRecover(
                GameRepository.GetGameFetch(),
                currentStaminaResult,
                maxStamina,
                dailyBuyStaminaAdIntervalMinutes,
                maxDailyBuyStaminaAdCount,
                TimeProvider.Now
            );

            var adPercentage = MstConfigRepository.GetConfig(MstConfigKey.BuyStaminaAdPercentageOfMaxStamina);
            var diamondPercentage = MstConfigRepository.GetConfig(MstConfigKey.BuyStaminaDiamondPercentageOfMaxStamina);

            var adRecoverStaminaAmount = StaminaRecoverCalculator.CalcAdRecoverValue(
                adPercentage.Value.ToStaminaRecoverPercentage(),
                maxStamina);

            var diamondRecoverStaminaAmount = StaminaRecoverCalculator.CalcAdRecoverValue(
                diamondPercentage.Value.ToStaminaRecoverPercentage(),
                maxStamina);

            var remainingAdRecoverCount = BuyStaminaAdCount.Max(
                maxDailyBuyStaminaAdCount - gameFetchModel.UserBuyCountModel.DailyBuyStaminaAdCount,
                BuyStaminaAdCount.Zero);

            var remainingNextAdReceivableStaminaTime = StaminaRecoverCalculator.CalcReceivableTime(
                gameFetchModel,
                maxStamina,
                dailyBuyStaminaAdIntervalMinutes,
                maxDailyBuyStaminaAdCount,
                TimeProvider.Now);


            return new UserStaminaModel(
                currentStaminaResult.CurrentStamina,
                maxStamina,
                adRecoverStaminaAmount,
                diamondRecoverStaminaAmount,
                remainingAdRecoverCount,
                recoveryStaminaMinutes,
                remainFullRecoverySeconds,
                currentStaminaResult.RemainUpdatingStaminaRecoverSecond,
                canRecoverByAd,
                userParameterModel.StaminaUpdatedAt,
                remainingNextAdReceivableStaminaTime,
                !additionalStaminaLimit.IsZero() ?
                    HeldAdditionalStaminaPassEffectFlag.True :
                    HeldAdditionalStaminaPassEffectFlag.False);
        }
    }
}
