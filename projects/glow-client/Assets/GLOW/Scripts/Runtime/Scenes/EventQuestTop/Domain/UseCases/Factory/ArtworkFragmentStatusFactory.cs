using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class ArtworkFragmentStatusFactory : IArtworkFragmentStatusFactory
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        ArtworkFragmentStatusModel IArtworkFragmentStatusFactory.Create(
            IReadOnlyList<MstQuestModel> mstQuestModels)
        {
            var stages = mstQuestModels
                .Distinct(mst => mst.Id)
                .SelectMany(mst => MstStageDataRepository.GetMstStagesFromMstQuestId(mst.Id))
                .ToList();
            return GetStatus(stages);
        }

        ArtworkFragmentStatusModel GetStatus(IReadOnlyList<MstStageModel> targetStages)
        {
            var distinctTargetStages = targetStages
                .Distinct(t => t.MstArtworkFragmentDropGroupId)
                .Select(t =>t.MstArtworkFragmentDropGroupId)
                .ToList();
            var mstFragments = MstArtworkFragmentDataRepository.GetArtworkFragmentsGroupByMstDropGroupId();

            // 取得可能
            var gettableArtworkFragments = distinctTargetStages
                .Join(
                    mstFragments,
                    stage => stage,
                    fragment => fragment.Key,
                    (_, fragments) => fragments)
                .Sum(s => s.Count());

            //取得可能無ければ無いものとしてreturnする
            if (gettableArtworkFragments == 0)
            {
                return ArtworkFragmentStatusModel.Empty;
            }

            var fetchOtherModel = GameRepository.GetGameFetchOther();
            // 取得済み
            var acquiredArtworkFragments = distinctTargetStages.Sum(m => GetAcquired(mstFragments, fetchOtherModel.UserArtworkFragmentModels,m));

            return new ArtworkFragmentStatusModel(
                new ArtworkFragmentNum(gettableArtworkFragments),
                new ArtworkFragmentNum(acquiredArtworkFragments)
            );
        }

        int GetAcquired(
            IReadOnlyList<IGrouping<MasterDataId, MstArtworkFragmentModel>> mstFragments,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            MasterDataId mstArtworkFragmentDropGroupId)
        {
            var artworkFragmentList =
                mstFragments
                    .FirstOrDefault(c => c.Key == mstArtworkFragmentDropGroupId);
            if (artworkFragmentList == null) return 0;

            var hashSet = userArtworkFragmentModels
                .Select(m => m.MstArtworkFragmentId)
                .ToHashSet();

            var acquired = artworkFragmentList
                .Count(target => HasArtworkFragment(target, hashSet));

            return acquired;
        }

        bool HasArtworkFragment(MstArtworkFragmentModel target, HashSet<MasterDataId> hashSet)
        {
            return hashSet.Contains(target.Id);
        }

    }
}
