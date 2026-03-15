using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Factory
{
    public class ArtworkPanelMissionResultModelFactory : IArtworkPanelMissionResultModelFactory
    {
        [Inject] IMstMissionDataRepository MstMissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        ArtworkPanelMissionFetchResultModel IArtworkPanelMissionResultModelFactory.CreateArtworkPanelMissionResultModel(
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            MasterDataId mstArtworkPanelMissionId)
        {
            var mstMissionLimitedTerm = MstMissionDataRepository.GetMstMissionLimitedTermModels()
                .Where(model => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    model.StartDate.Value,
                    model.EndDate.Value))
                .Where(model => model.MissionCategory == MissionCategory.ArtworkPanel)
                .Where(model => model.MissionProgressGroupKey.ToMasterDataId() == mstArtworkPanelMissionId);
            var limitedTermList = mstMissionLimitedTerm
                .GroupJoin(userMissionLimitedTermModels,
                    mst => mst.Id,
                    user => user.MstMissionLimitedTermId,
                    (mst, users) => new { mst, user = users.FirstOrDefault() ?? UserMissionLimitedTermModel.Empty })
                .Select(mstAndUser => CreateArtworkPanelMissionCellModel(
                    userMissionLimitedTermModels,
                    mstAndUser.mst, 
                    mstAndUser.user))
                .Where(cell => !cell.IsEmpty())
                .OrderBy(cell => cell.MissionStatus)
                .ThenBy(cell => cell.SortOrder)
                .ToList();
            
            return new ArtworkPanelMissionFetchResultModel(limitedTermList);
        }
        
        ArtworkPanelMissionCellModel CreateArtworkPanelMissionCellModel(
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            MstMissionLimitedTermModel mst,
            UserMissionLimitedTermModel user)
        {
            var dependencyIdList = MstMissionDataRepository
                .GetMstMissionLimitedTermDependencyModels()
                .Where(dependency => dependency.GroupId == mst.GroupId && dependency.UnlockOrder < mst.UnlockOrder)
                .Select(model => model.MstMissionLimitedTermId)
                .Join(userMissionLimitedTermModels,
                    dependency => dependency,
                    userModel => userModel.MstMissionLimitedTermId,
                    (dependency, userModel) => new { dependency, userModel });

            // Dependencyで設定されているミッションの場合、それを下回るUnlockOrderが設定されているミッションを全てクリアしていない場合はEmptyを返す(表示しない)
            var isNotAllClear = dependencyIdList.Any(id => !id.userModel.IsCleared);
            if (isNotAllClear)
            {
                return ArtworkPanelMissionCellModel.Empty;
            }

            var rewardModels = MstMissionDataRepository
                .GetMissionRewardModelList(mst.MstMissionRewardGroupId)
                .Select(model => PlayerResourceModelFactory.Create(
                    model.ResourceType,
                    model.ResourceId,
                    model.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();
            var artworkFragmentReward = rewardModels.FirstOrDefault(
                reward => reward.Type == ResourceType.ArtworkFragment,
                PlayerResourceModel.Empty);
            var otherReward = rewardModels.FirstOrDefault(
                reward => reward.Type != ResourceType.ArtworkFragment, 
                PlayerResourceModel.Empty);
            
            return new ArtworkPanelMissionCellModel(
                mst.Id,
                MissionType.LimitedTerm,
                MissionCategory.ArtworkPanel,
                MissionStatusTranslator.ToMissionStatus(user.IsCleared, user.IsReceivedReward),
                user.Progress,
                mst.CriterionCount,
                artworkFragmentReward,
                otherReward,
                mst.MissionDescription,
                mst.SortOrder,
                mst.DestinationScene);
        }
    }
}