using System;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;
using GLOW.Scenes.StaminaRecover.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class CreateStaminaTradeUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstUserLevelDataRepository MstUserLevelDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public StaminaTradeUseCaseModel CreateStaminaTradeUseCaseModel(MasterDataId mstItemId)
        {
            var name = MstItemDataRepository.GetItem(mstItemId).Name;

            var userCurrentStamina = GameRepository.GetGameFetch().UserParameterModel.CurrentStamina;

            var effectValue = GetItemEffectValue(mstItemId);

            var maxPurchasableCount = GetMaxPurchasableCount(mstItemId, effectValue);

            var playerResourceModel = GetItemResourceModel(mstItemId);

            var maxStamina = MstConfigRepository.GetConfig(MstConfigKey.UserMaxStaminaAmount).Value.ToInt();

            return new StaminaTradeUseCaseModel(
                mstItemId,
                name,
                effectValue,
                userCurrentStamina,
                maxPurchasableCount,
                playerResourceModel,
                new Stamina(maxStamina)
            );
        }

        Stamina GetItemEffectValue(MasterDataId mstItemId)
        {
            var item = MstItemDataRepository.GetItem(mstItemId);
            var effectValue = item.EffectValue.ToStamina();

            if(item.Type == ItemType.StaminaRecoveryFixed) return effectValue;

            var userParameter = GameRepository.GetGameFetch().UserParameterModel;
            var currentUserStaminaMax =MstUserLevelDataRepository
                .GetUserLevelModel(userParameter.Level).MaxStamina.Value;

            var calculatedStamina = (int)(currentUserStaminaMax * ((float)effectValue.Value / 100));
            return new Stamina(calculatedStamina);
        }

        PurchasableCount GetMaxPurchasableCount(MasterDataId mstItemId ,Stamina effectValue)
        {
            var purchasableCount = PurchasableCount.Empty;

            var currentUserStamina = GameRepository.GetGameFetch().UserParameterModel.CurrentStamina.Value;
            var maxUserStamina = MstConfigRepository.GetConfig(MstConfigKey.UserMaxStaminaAmount).Value.ToInt();

            if(currentUserStamina >= maxUserStamina) return purchasableCount;

            var possibleRecoverStamina = maxUserStamina - currentUserStamina;
            if(possibleRecoverStamina <= 0) return purchasableCount;

            // スタミナ上限に完全に達するまでの使用回数を計算する
            int maxUseableCount = (int)Math.Ceiling((double)possibleRecoverStamina / effectValue.Value);

            var userHasItemCount = GameRepository.GetGameFetchOther().UserItemModels
                .FirstOrDefault(item => item.MstItemId == mstItemId, UserItemModel.Empty)
                .HasAmount;

            purchasableCount = new PurchasableCount(Math.Min(maxUseableCount, userHasItemCount));

            return purchasableCount;
        }

        PlayerResourceModel GetItemResourceModel(MasterDataId mstItemId)
        {
            var playerHasAmount = GameRepository.GetGameFetchOther().UserItemModels
                .FirstOrDefault(item => item.MstItemId == mstItemId, UserItemModel.Empty)
                .HasAmount;

            return PlayerResourceModelFactory.Create(ResourceType.Item, mstItemId, new PlayerResourceAmount(playerHasAmount));
        }
    }
}
