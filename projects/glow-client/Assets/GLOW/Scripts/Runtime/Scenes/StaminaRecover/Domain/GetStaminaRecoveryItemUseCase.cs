using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.StaminaRecover.Domain.UseCaseModel;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class GetStaminaRecoveryItemUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IMstUserLevelDataRepository MstUserLevelDataRepository { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public IReadOnlyList<StaminaRecoverySelectCellUseCaseModel> GetStaminaRecoveryItems()
        {
            // 全体で必要な情報を先に取り出す(現在のスタミナ、スタミナの上限、最大スタミナ上限)
            var userParameter = GameRepository.GetGameFetch().UserParameterModel;
            var currentUserStaminaMax = MstUserLevelDataRepository
                .GetUserLevelModel(userParameter.Level).MaxStamina.Value;
            var maxUserStamina = MstConfigRepository.GetConfig(MstConfigKey.UserMaxStaminaAmount).Value.ToInt();

            var gameFetchOther = GameRepository.GetGameFetchOther();

            // 最初に広告とプリズムのモデルを手動で追加
            List<StaminaRecoverySelectCellUseCaseModel> staminaRecoveryItems = new List<StaminaRecoverySelectCellUseCaseModel>();
            staminaRecoveryItems.Add(CreateAdUseCaseModel(currentUserStaminaMax, maxUserStamina));
            staminaRecoveryItems.Add(CreateDiamondUseCaseModel(currentUserStaminaMax));

            // スタミナ回復アイテムのマスターデータを取得してモデルを作成
            var items = MstItemDataRepository.GetItems()
                .Where(item => item.Type == ItemType.StaminaRecoveryPercent
                               || item.Type == ItemType.StaminaRecoveryFixed)
                .Where(item => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    item.StartAt,
                    item.EndAt))
                .GroupJoin(
                    gameFetchOther.UserItemModels,
                    group => group.Id,
                    item => item.MstItemId,
                    (group, data)
                        => new { group, userItemModel = data.FirstOrDefault() ?? UserItemModel.Empty })
                .Select(item =>
                    CreateUseCaseModel(item.group, currentUserStaminaMax, item.userItemModel))
                .Where(item => !item.IsEmpty())
                .ToList();

            staminaRecoveryItems.AddRange(items);

            return staminaRecoveryItems;
        }

        StaminaRecoverySelectCellUseCaseModel CreateUseCaseModel(
            MstItemModel mstItemModel,
            int currentUserStaminaMax,
            UserItemModel userItemModel)
        {
            var itemAmount = userItemModel.Amount;

            // EffectValueが空の場合は表示がおかしくなるので無効なアイテムとして扱う
            var effectValue = GetEffectValue(mstItemModel, currentUserStaminaMax);
            if (effectValue.IsEmpty()) return StaminaRecoverySelectCellUseCaseModel.Empty;

            var availability = StaminaRecoveryAvailability.Available;
            if (itemAmount.Value <= 0)
            {
                availability = StaminaRecoveryAvailability.Unavailable;
            }

            var staminaRecoveryAvailableStatus =
                new StaminaRecoveryAvailableStatus(
                    StaminaRecoveryType.Item,
                    BuyStaminaAdCount.Empty,
                    itemAmount);

            return new StaminaRecoverySelectCellUseCaseModel(
                mstItemModel.Id,
                mstItemModel.Name,
                effectValue,
                ItemAmount.One,
                staminaRecoveryAvailableStatus,
                availability,
                RemainingTimeSpan.Empty, // スタミナ回復アイテムにリセット時間はない
                mstItemModel.ItemAssetKey);
        }

        Stamina GetEffectValue(MstItemModel mstItemModel, int currentUserStaminaMax)
        {
            var itemType = mstItemModel.Type;
            if (itemType != ItemType.StaminaRecoveryPercent
                && itemType != ItemType.StaminaRecoveryFixed)
            {
                return Stamina.Empty;
            }

            if (mstItemModel.EffectValue.IsEmpty()) return Stamina.Empty;

            var effectValue = mstItemModel.EffectValue.ToStamina();

            if (itemType == ItemType.StaminaRecoveryPercent)
            {
                var actualEffectValue = (int)(currentUserStaminaMax * ((float)effectValue.Value / 100));
                return new Stamina(actualEffectValue);
            }
            else
            {
                return effectValue;
            }
        }

        StaminaRecoverySelectCellUseCaseModel CreateAdUseCaseModel(int currentUserStaminaMax, int maxUserStamina)
        {
            // 広告の残り視聴回数を計算
            var adCount = MstConfigRepository.GetConfig(MstConfigKey.MaxDailyBuyStaminaAdCount).Value.ToInt();
            var userBuyCountModel = GameRepository.GetGameFetch().UserBuyCountModel;
            var adRemainingCount = adCount - userBuyCountModel.DailyBuyStaminaAdCount.Value;

            // 広告の効果量を計算
            var adEffectPercentage = MstConfigRepository
                .GetConfig(MstConfigKey.BuyStaminaAdPercentageOfMaxStamina).Value.ToInt();
            var adEffectValue = (int)(currentUserStaminaMax * ((float)adEffectPercentage / 100));

            // 広告スキップパスの情報を取得
            var adSkipPass = HeldPassEffectRepository.GetHeldPassEffectListModel().PassEffectModels
                .FirstOrDefault(pass => pass.ShopPassEffectType == ShopPassEffectType.AdSkip,
                    HeldPassEffectModel.Empty);

            // 広告用のステータス
            var adAvailableStatus =
                new StaminaRecoveryAvailableStatus(
                    adSkipPass.IsEmpty()
                        ? StaminaRecoveryType.Ad
                        : StaminaRecoveryType.AdSkip,
                    new BuyStaminaAdCount(adRemainingCount),
                    ItemAmount.Empty);

            // 広告の残り視聴時間を取得
            var adRemainingTime = GetAdRemainingTime(adAvailableStatus, currentUserStaminaMax, maxUserStamina);

            // 広告による交換可能状態を取得
            var adAvailability = GetAdAvailability(adAvailableStatus, adRemainingTime);

            return // 広告視聴のモデル
                new StaminaRecoverySelectCellUseCaseModel(
                    new MasterDataId("ad_stamina_recovery"),
                    new ItemName("広告視聴"),
                    new Stamina(adEffectValue),
                    ItemAmount.One,
                    adAvailableStatus,
                    adAvailability,
                    adRemainingTime,
                    ItemAssetKey.Empty);
        }

        StaminaRecoverySelectCellUseCaseModel CreateDiamondUseCaseModel(int currentUserStaminaMax)
        {
            var userParameter = GameRepository.GetGameFetch().UserParameterModel;
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            var diamondAmount = userParameter.GetTotalDiamond(platformId);

            // プリズムによる交換可能状態を取得
            var availability = GetDiamondAvailability(diamondAmount.Value);

            var diamondEffectPercentage = MstConfigRepository
                .GetConfig(MstConfigKey.BuyStaminaDiamondPercentageOfMaxStamina).Value.ToInt();
            var diamondEffectValue = (int)(currentUserStaminaMax * ((float)diamondEffectPercentage / 100));

            // プリズム用のステータス
            var prismAvailableStatus =
                new StaminaRecoveryAvailableStatus(
                    StaminaRecoveryType.Diamond,
                    BuyStaminaAdCount.Empty,
                    new ItemAmount(diamondAmount.Value));

            // プリズム必要数
            var prismRequiredAmount = new ItemAmount(
                MstConfigRepository.GetConfig(MstConfigKey.BuyStaminaDiamondAmount).Value.ToInt());

            // 広告とプリズムは回復する値が決まってるので、判別するために-1を設定してモデルを作成
            return // プリズムのモデル
                new StaminaRecoverySelectCellUseCaseModel(
                    new MasterDataId("diamond_stamina_recovery"),
                    new ItemName("ダイヤ消費"),
                    new Stamina(diamondEffectValue),
                    prismRequiredAmount,
                    prismAvailableStatus,
                    availability,
                    RemainingTimeSpan.Empty,
                    new ItemAssetKey(new DiamondAssetKey().Value));
        }

        StaminaRecoveryAvailability GetAdAvailability(
            StaminaRecoveryAvailableStatus adStatus,
            RemainingTimeSpan remainingTime)
        {
            if (adStatus.BuyAdCount.Value <= 0) return StaminaRecoveryAvailability.Unavailable;

            if (!remainingTime.IsEmpty()) return StaminaRecoveryAvailability.WaitingForReset;

            return StaminaRecoveryAvailability.Available;
        }

        StaminaRecoveryAvailability GetDiamondAvailability(int diamondAmount)
        {
            var availability = StaminaRecoveryAvailability.Available;
            if (diamondAmount <= 0)
            {
                availability = StaminaRecoveryAvailability.Unavailable;
            }

            return availability;
        }

        RemainingTimeSpan GetAdRemainingTime(
            StaminaRecoveryAvailableStatus adStatus,
            int currentUserStaminaMax,
            int maxUserStamina)
        {
            if (currentUserStaminaMax >= maxUserStamina) return RemainingTimeSpan.Empty;

            if (adStatus.BuyAdCount.Value <= 0) return RemainingTimeSpan.Empty;

            var adRecoveryIntervalMinutes = MstConfigRepository
                .GetConfig(MstConfigKey.DailyBuyStaminaAdIntervalMinutes).Value
                .ToRecoveryStaminaMinutes()
                .ToTimeSpan();

            var adRecoverableTime = GameRepository.GetGameFetch().UserBuyCountModel.DailyBuyStaminaAdAt;
            if (adRecoverableTime == null) return RemainingTimeSpan.Empty;

            return TimeProvider.Now - adRecoverableTime.Value < adRecoveryIntervalMinutes
                ? new RemainingTimeSpan(adRecoveryIntervalMinutes - (TimeProvider.Now - adRecoverableTime.Value))
                : RemainingTimeSpan.Empty;
        }
    }
}


