using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Extensions
{
    public static class GameFetchOtherModelPropertyExtension
    {
        public static IReadOnlyList<UserUnitModel> Update(
            this IReadOnlyList<UserUnitModel> models,
            IReadOnlyList<UserUnitModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedIds = filteredUpdatedModels.Select(model => model.MstUnitId).ToHashSet();

            return models
                .Where(model => !updatedIds.Contains(model.MstUnitId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserUnitModel> Update(
            this IReadOnlyList<UserUnitModel> models,
            UserUnitModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.MstUnitId == updatedModel.MstUnitId, updatedModel);
        }

        public static IReadOnlyList<UserItemModel> Update(
            this IReadOnlyList<UserItemModel> models,
            IReadOnlyList<UserItemModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedIds = filteredUpdatedModels.Select(model => model.MstItemId).ToHashSet();

            return models
                .Where(model => !updatedIds.Contains(model.MstItemId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserShopItemModel> Update(
            this IReadOnlyList<UserShopItemModel> models,
            IReadOnlyList<UserShopItemModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedIds = filteredUpdatedModels.Select(model => model.MstShopItemId).ToHashSet();

            return models
                .Where(model => !updatedIds.Contains(model.MstShopItemId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserStoreProductModel> Update(
            this IReadOnlyList<UserStoreProductModel> models,
            UserStoreProductModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.ProductSubId == updatedModel.ProductSubId, updatedModel);
        }

        public static IReadOnlyList<UserConditionPackModel> Update(
            this IReadOnlyList<UserConditionPackModel> models,
            IReadOnlyList<UserConditionPackModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedIds = filteredUpdatedModels.Select(model => model.MstPackId).ToHashSet();

            return models
                .Where(model => !updatedIds.Contains(model.MstPackId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserPartyModel> Update(
            this IReadOnlyList<UserPartyModel> models,
            IReadOnlyList<UserPartyModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedPartyNoList = filteredUpdatedModels.Select(model => model.PartyNo).ToHashSet();

            return models
                .Where(model => !updatedPartyNoList.Contains(model.PartyNo))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserHomeOutpostModel> Update(
            this IReadOnlyList<UserHomeOutpostModel> models,
            UserHomeOutpostModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.MstOutpostId == updatedModel.MstOutpostId, updatedModel);
        }

        public static IReadOnlyList<UserOutpostEnhanceModel> Update(
            this IReadOnlyList<UserOutpostEnhanceModel> models,
            UserOutpostEnhanceModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(
                model =>
                    model.MstOutpostId == updatedModel.MstOutpostId &&
                    model.MstOutpostEnhanceId == updatedModel.MstOutpostEnhanceId,
                updatedModel);
        }

        public static IReadOnlyList<UserEmblemModel> Update(
            this IReadOnlyList<UserEmblemModel> models,
            IReadOnlyList<UserEmblemModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedEmblemIds = filteredUpdatedModels.Select(model => model.MstEmblemId).ToHashSet();

            return models
                .Where(model => !updatedEmblemIds.Contains(model.MstEmblemId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserArtworkModel> Update(
            this IReadOnlyList<UserArtworkModel> models,
            IReadOnlyList<UserArtworkModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedArtworkIds = filteredUpdatedModels.Select(model => model.MstArtworkId).ToHashSet();

            return models
                .Where(model => !updatedArtworkIds.Contains(model.MstArtworkId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserArtworkFragmentModel> Update(
            this IReadOnlyList<UserArtworkFragmentModel> models,
            IReadOnlyList<UserArtworkFragmentModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty());

            return models
                .Concat(filteredUpdatedModels)
                .Distinct()
                .ToList();
        }

        public static IReadOnlyList<UserGachaModel> Update(
            this IReadOnlyList<UserGachaModel> models,
            UserGachaModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.OprGachaId == updatedModel.OprGachaId, updatedModel);
        }

        public static IReadOnlyList<UserGachaModel> Update(
            this IReadOnlyList<UserGachaModel> models,
            IReadOnlyList<UserGachaModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedModelsIds = filteredUpdatedModels.Select(model => model.OprGachaId).ToHashSet();

            return models
                .Where(model => !updatedModelsIds.Contains(model.OprGachaId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserDrawCountThresholdModel> Update(
            this IReadOnlyList<UserDrawCountThresholdModel> models,
            IReadOnlyList<UserDrawCountThresholdModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedGroupIds = filteredUpdatedModels.Select(model => model.DrawCountThresholdGroupId).ToHashSet();

            return models
                .Where(model => !updatedGroupIds.Contains(model.DrawCountThresholdGroupId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> Update(
            this IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> models,
            IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty());

            return models
                .Concat(filteredUpdatedModels)
                .Distinct()
                .ToList();
        }

        public static IReadOnlyList<UserEnemyDiscoverModel> Update(
            this IReadOnlyList<UserEnemyDiscoverModel> models,
            IReadOnlyList<UserEnemyDiscoverModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedEnemyIds = filteredUpdatedModels.Select(model => model.MstEnemyCharacterId).ToHashSet();

            return models
                .Where(model => !updatedEnemyIds.Contains(model.MstEnemyCharacterId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserItemTradeModel> Update(
            this IReadOnlyList<UserItemTradeModel> models ,
            UserItemTradeModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.MstItemId == updatedModel.MstItemId, updatedModel);
        }

        public static IReadOnlyList<UserShopPassModel> Update(
            this IReadOnlyList<UserShopPassModel> models,
            UserShopPassModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.MstShopPassId == updatedModel.MstShopPassId, updatedModel);
        }

        public static IReadOnlyList<UserTradePackModel> Update(
            this IReadOnlyList<UserTradePackModel> models,
            IReadOnlyList<UserTradePackModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();
            var updatedEnemyIds = filteredUpdatedModels.Select(model => model.MstPackId).ToHashSet();

            return models
                .Where(model => !updatedEnemyIds.Contains(model.MstPackId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }

        public static IReadOnlyList<UserExchangeLineupModel> Update(
            this IReadOnlyList<UserExchangeLineupModel> models,
            IReadOnlyList<UserExchangeLineupModel> updatedModels)
        {
            var updateIds = updatedModels
                .Select(model => (model.MstExchangeId, model.MstExchangeLineupId))
                .ToHashSet();

            return models
                .Where(model => !updateIds.Contains((model.MstExchangeId, model.MstExchangeLineupId)))
                .Concat(updatedModels)
                .ToList();
        }
    }
}
