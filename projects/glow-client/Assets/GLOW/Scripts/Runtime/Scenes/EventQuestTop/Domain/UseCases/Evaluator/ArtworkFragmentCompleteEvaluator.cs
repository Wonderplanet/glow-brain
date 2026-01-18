using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class ArtworkFragmentCompleteEvaluator : IArtworkFragmentCompleteEvaluator
    {
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        StageRewardCompleteFlag IArtworkFragmentCompleteEvaluator.Evaluate(MasterDataId mstArtworkFragmentDropGroupId)
        {
            var mstArtworkFragmentModels = MstArtworkFragmentDataRepository.GetDropGroupArtworkFragments(mstArtworkFragmentDropGroupId);
            var userArtworkFragmentModels = GameRepository.GetGameFetchOther().UserArtworkFragmentModels;
            return IsComplete(mstArtworkFragmentModels, userArtworkFragmentModels);
        }

        StageRewardCompleteFlag IsComplete(
            IReadOnlyList<MstArtworkFragmentModel> mstArtworkFragmentModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            if (!mstArtworkFragmentModels.Any()) return StageRewardCompleteFlag.False;

            var hashSet = userArtworkFragmentModels
                .Select(m => m.MstArtworkFragmentId)
                .ToHashSet();

            foreach (var mstFragment in mstArtworkFragmentModels)
            {
                if (!hashSet.Contains(mstFragment.Id))
                {
                    return StageRewardCompleteFlag.False;
                }
            }

            return StageRewardCompleteFlag.True;
        }
    }
}
