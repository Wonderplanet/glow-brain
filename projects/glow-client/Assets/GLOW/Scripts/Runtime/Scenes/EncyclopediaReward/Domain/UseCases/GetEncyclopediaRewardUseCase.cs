using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaReward.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaReward.Domain.UseCases
{
    public class GetEncyclopediaRewardUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public EncyclopediaRewardModel GetRewardList()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var receivedUnitEncyclopediaRewards = gameFetchOther.UserReceivedUnitEncyclopediaRewardModels;
            var currentUserEncyclopediaGrade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(gameFetchOther.UserUnitModels);

            var mstUnitEncyclopediaRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards()
                .OrderByDescending(model => model.UnitEncyclopediaRank.Value)
                .ToList();

            var releasedRewards = mstUnitEncyclopediaRewards
                .Where(mst => mst.UnitEncyclopediaRank <= currentUserEncyclopediaGrade)
                .ToList();
            var lockedRewards = mstUnitEncyclopediaRewards
                .Where(mst => mst.UnitEncyclopediaRank > currentUserEncyclopediaGrade)
                .ToList();

            var releasedCells = releasedRewards
                .Select(model => TranslateReleasedCellModel(model, receivedUnitEncyclopediaRewards))
                .ToList();
            var lockedCells = lockedRewards
                .Select(TranslateLockCellModel)
                .ToList();

            var currentRank = new EncyclopediaUnitGrade(currentUserEncyclopediaGrade.Value);

            return new EncyclopediaRewardModel(currentRank, releasedCells, lockedCells);
        }

        EncyclopediaRewardListCellModel TranslateReleasedCellModel(
            MstUnitEncyclopediaRewardModel model,
            IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> receivedRewards)
        {
            var rewardItem = PlayerResourceModelFactory.Create(
                model.ResourceType, 
                model.ResourceId, 
                model.ResourceAmount.ToPlayerResourceAmount());
            
            MstUnitEncyclopediaEffectModel mstUnitEncyclopediaEffect = MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffect(model.Id);
            var isReceived = receivedRewards.Any(id => id.MstUnitEncyclopediaRewardId == model.Id);
            var notificationBadge = new NotificationBadge(!isReceived);
            var receivedFlag = new ReceivedFlag(isReceived);

            return new EncyclopediaRewardListCellModel(
                model.Id,
                rewardItem,
                new EncyclopediaUnitGrade(model.UnitEncyclopediaRank.Value),
                mstUnitEncyclopediaEffect?.EffectType ?? UnitEncyclopediaEffectType.AttackPower,
                mstUnitEncyclopediaEffect?.Value ?? new UnitEncyclopediaEffectValue(10),
                notificationBadge,
                receivedFlag);
        }

        EncyclopediaRewardListCellModel TranslateLockCellModel(MstUnitEncyclopediaRewardModel model)
        {
            var rewardItem = PlayerResourceModelFactory.Create(
                model.ResourceType, 
                model.ResourceId, 
                model.ResourceAmount.ToPlayerResourceAmount());
            
            MstUnitEncyclopediaEffectModel mstUnitEncyclopediaEffect = MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffect(model.Id);

            return new EncyclopediaRewardListCellModel(
                model.Id,
                rewardItem,
                new EncyclopediaUnitGrade(model.UnitEncyclopediaRank.Value),
                mstUnitEncyclopediaEffect?.EffectType ?? UnitEncyclopediaEffectType.Hp,
                mstUnitEncyclopediaEffect?.Value ?? new UnitEncyclopediaEffectValue(0.1m),
                NotificationBadge.False,
                new ReceivedFlag(false));
        }
    }
}
