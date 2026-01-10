using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class EncyclopediaService : IEncyclopediaService
    {
        [Inject] EncyclopediaApi EncyclopediaApi { get; }

        public async UniTask<EncyclopediaReceiveRewardResultModel> ReceiveReward(
            CancellationToken cancellationToken,
            IReadOnlyList<MasterDataId> mstUnitEncyclopediaRewardIds)
        {
            var ids = mstUnitEncyclopediaRewardIds
                .Select(id => id.ToString())
                .ToArray();

            var result = await EncyclopediaApi.ReceiveReward(cancellationToken, ids);
            return Translate(result);
        }

        public async UniTask<EncyclopediaReceiveFirstCollectionRewardResultModel> ReceiveFirstCollectionReward(
            CancellationToken cancellationToken,
            EncyclopediaType type,
            MasterDataId mstId)
        {
            var result = await EncyclopediaApi.ReceiveFirstCollectionReward(
                cancellationToken,
                type.ToString(),
                mstId.Value
                );

            return Translate(result);
        }

        EncyclopediaReceiveRewardResultModel Translate(EncyclopediaReceiveRewardResultData data)
        {
            var userReceiveRewards = data.UsrReceivedUnitEncyclopediaRewards
                .Select(UserReceivedUnitEncyclopediaRewardDataTranslator.TranslateToModel)
                .ToList();
            var playerResourceResults = data.UnitEncyclopediaRewards
                .Select(UnitEncyclopediaRewardDataTranslator.TranslateToPlayerResourceResult)
                .ToList();
            var isEmblemDuplicated = new IsEmblemDuplicated(data.IsEmblemDuplicated);
            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var userItems = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();
            var userLevelUp = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            var userConditionPacks = data.UsrConditionPacks
                .Select(UserConditionPackDataTranslator.ToModel)
                .ToList();

            return new EncyclopediaReceiveRewardResultModel(
                userReceiveRewards,
                playerResourceResults,
                userParameter,
                userItems,
                userLevelUp,
                userConditionPacks,
                isEmblemDuplicated);
        }

        EncyclopediaReceiveFirstCollectionRewardResultModel Translate(EncyclopediaReceiveFirstCollectionRewardResultData data)
        {
            var userEmblems = data.UsrEmblems
                .Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList();

            var userArtworks = data.UsrArtworks
                .Select(UserArtworkDataTranslator.ToUserArtworkModel)
                .ToList();

            var userEnemyDiscoveries = data.UsrEnemyDiscoveries
                .Select(UserEnemyDiscoverDataTranslator.Translate)
                .ToList();

            var userUnits = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var resourceResults = data.EncyclopediaFirstCollectionRewards
                .Select(r => RewardDataTranslator.Translate(r.Reward))
                .ToList();

            return new EncyclopediaReceiveFirstCollectionRewardResultModel(
                userParameter,
                resourceResults,
                userEmblems,
                userArtworks,
                userEnemyDiscoveries,
                userUnits);
        }
    }
}
