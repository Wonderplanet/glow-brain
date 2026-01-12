using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.IdleIncentive;
using GLOW.Core.Domain.Services;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class IdleIncentiveService : IIdleIncentiveService
    {
        [Inject] IdleIncentiveApi IdleIncentiveApi { get; }

        public async UniTask<IdleIncentiveReceiveResultModel> Receive(CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveApi.Receive(cancellationToken);
            return TranslateReceiveResult(result);
        }

        public async UniTask<IdleIncentiveReceiveResultModel> QuickReceiveByItem(
            CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveApi.QuickReceiveByDiamond(cancellationToken);
            return TranslateQuickReceiveByItemModel(result);
        }

        public async UniTask<IdleIncentiveReceiveResultModel> QuickReceiveByAd(CancellationToken cancellationToken)
        {
            var result = await IdleIncentiveApi.QuickReceiveByAd(cancellationToken);
            return TranslateQuickReceiveByAdResult(result);
        }

        IdleIncentiveReceiveResultModel TranslateReceiveResult(IdleIncentiveReceiveResultData data)
        {
            var rewards = data.Rewards.Select(r => RewardDataTranslator.Translate(r.Reward)).ToList();
            var userLevel = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var items = data.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            var userIdleIncentive = UserIdleIncentiveDataTranslator.ToModel(data.UsrIdleIncentive);
            var userConditionPacks = data.UsrConditionPacks.Select(UserConditionPackDataTranslator.ToModel).ToList();

            return new IdleIncentiveReceiveResultModel(
                rewards,
                userLevel,
                userIdleIncentive,
                userParameter,
                items,
                userConditionPacks);
        }

        IdleIncentiveReceiveResultModel TranslateQuickReceiveByAdResult(IdleIncentiveQuickReceiveByAdResultData data)
        {
            var rewards = data.Rewards.Select(r => RewardDataTranslator.Translate(r.Reward)).ToList();
            var userLevel = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var items = data.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            var userIdleIncentive = UserIdleIncentiveDataTranslator.ToModel(data.UsrIdleIncentive);
            var userConditionPacks = data.UsrConditionPacks.Select(UserConditionPackDataTranslator.ToModel).ToList();

            return new IdleIncentiveReceiveResultModel(
                rewards,
                userLevel,
                userIdleIncentive,
                userParameter,
                items,
                userConditionPacks);
        }

        IdleIncentiveReceiveResultModel TranslateQuickReceiveByItemModel(
            IdleIncentiveQuickReceiveByDiamondResultData data)
        {
            var rewards = data.Rewards.Select(r => RewardDataTranslator.Translate(r.Reward)).ToList();
            var userLevel = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var items = data.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            var userIdleIncentive = UserIdleIncentiveDataTranslator.ToModel(data.UsrIdleIncentive);
            var userConditionPacks = data.UsrConditionPacks.Select(UserConditionPackDataTranslator.ToModel).ToList();

            return new IdleIncentiveReceiveResultModel(
                rewards,
                userLevel,
                userIdleIncentive,
                userParameter,
                items,
                userConditionPacks);
        }
    }
}
