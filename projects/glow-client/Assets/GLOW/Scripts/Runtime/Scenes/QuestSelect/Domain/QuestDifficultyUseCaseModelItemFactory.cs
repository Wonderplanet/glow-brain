using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public class QuestDifficultyUseCaseModelItemFactory :
        IQuestDifficultyUseCaseModelItemFactory,
        IInitializable
    {
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        List<IGrouping<MasterDataId,MstArtworkFragmentModel>> _cachedMstArtworkFragmentModels;

        bool _initialized;
        void IInitializable.Initialize()
        {
            Initialize();
        }

        void Initialize()
        {
            _cachedMstArtworkFragmentModels = MstArtworkFragmentDataRepository.GetArtworkFragmentModels().GroupBy(m => m.MstDropGroupId).ToList();

            _initialized = true;
        }

        public IReadOnlyList<QuestSelectDifficultyUseCaseModel> CreateDifficultyItems(MstQuestModel targetMstQuestModel)
        {
            if (!_initialized) Initialize();
            var gameFetchModel = GameRepository.GetGameFetch();

            return MstQuestDataRepository.GetMstQuestModels()
                .Where(q => q.GroupId == targetMstQuestModel.GroupId)
                .Select(q => CreateQuestDifficultyUseCaseModelItem(q, gameFetchModel))
                .OrderBy(q =>
                {
                    //NOTE: 定義したリストの順番になるように並び替える
                    var difficultyList = new List<Difficulty>()
                    {
                        Difficulty.Normal,
                        Difficulty.Hard,
                        Difficulty.Extra
                    };
                    return difficultyList.IndexOf(q.Difficulty);
                })
                .ToList();
        }

        QuestSelectDifficultyUseCaseModel CreateQuestDifficultyUseCaseModelItem(MstQuestModel targetMstQuestModel, GameFetchModel gameFetchModel)
        {
            var targetStages = MstStageDataRepository.GetMstStages()
                .Where(s => s.MstQuestId == targetMstQuestModel.Id).ToList();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            // 原画のかけらの取得状況を取得
            var gettableArtworkFragments = targetStages.Sum(s =>
            {
                var artworkFragmentList =
                    _cachedMstArtworkFragmentModels
                        .FirstOrDefault(c => c.Key == s.MstArtworkFragmentDropGroupId);
                return artworkFragmentList != null ? artworkFragmentList.ToList().Count : 0;
            });

            var acquiredArtworkFragments = targetStages.Sum(s =>
            {
                var artworkFragmentList =
                    _cachedMstArtworkFragmentModels.FirstOrDefault(c => c.Key == s.MstArtworkFragmentDropGroupId);
                if (artworkFragmentList == null) return 0;

                var acquired = artworkFragmentList
                    .Count(art => gameFetchOtherModel.UserArtworkFragmentModels.Any(usr =>
                        art.MstArtworkId == usr.MstArtworkId && art.Id == usr.MstArtworkFragmentId));
                return acquired;
            });

            // その難易度を開放するためのステージが開放されているかを確認
            var difficultyReleaseTargetMstStage = targetStages
                .OrderBy(t => t.StageNumber.Value)
                .DefaultIfEmpty(MstStageModel.Empty)
                .First() ?? MstStageModel.Empty;

            return new QuestSelectDifficultyUseCaseModel(
                targetMstQuestModel.Id,
                targetMstQuestModel.Difficulty,
                IsOpenedDifficulty(targetMstQuestModel, difficultyReleaseTargetMstStage, targetMstQuestModel.QuestType, gameFetchModel)
                    ? QuestDifficultyOpenStatus.Released
                    : QuestDifficultyOpenStatus.NotRelease,
                CreateQuestDifficultyReleaseRequiredSentence(difficultyReleaseTargetMstStage),
                new ArtworkFragmentNum(gettableArtworkFragments),
                new ArtworkFragmentNum(acquiredArtworkFragments));
        }

        bool IsOpenedDifficulty(
            MstQuestModel mstQuestModel,
            MstStageModel mstStageModel,
            QuestType questType,
            GameFetchModel gameFetchModel)
        {
            var isOpenedMstQuest = mstQuestModel.StartDate <= TimeProvider.Now && TimeProvider.Now <= mstQuestModel.EndDate;
            if (!isOpenedMstQuest) return false;

            if (mstStageModel.ReleaseRequiredMstStageId.IsEmpty() ||
                string.IsNullOrEmpty(mstStageModel.ReleaseRequiredMstStageId.Value))
                return true;
            else
            {
                return questType switch
                {
                    QuestType.Normal => IsNormalDifficultyRelease(mstStageModel, gameFetchModel),
                    QuestType.Event => IsEventDifficultyRelease(mstStageModel, gameFetchModel),
                    _ => false
                };
            }
        }

        bool IsNormalDifficultyRelease(MstStageModel difficultyReleaseTargetMstStage, GameFetchModel gameFetchModel)
        {
            return gameFetchModel.StageModels
                .Where(s => 1 <= s.ClearCount.Value)
                .Exists(s => s.MstStageId == difficultyReleaseTargetMstStage.ReleaseRequiredMstStageId);
        }
        bool IsEventDifficultyRelease(MstStageModel difficultyReleaseTargetMstStage, GameFetchModel gameFetchModel)
        {
            return gameFetchModel.UserStageEventModels
                .Where(s => 1 <= s.ClearCount.Value)
                .Exists(s => s.MstStageId == difficultyReleaseTargetMstStage.ReleaseRequiredMstStageId);
        }

        QuestDifficultyReleaseRequiredSentence CreateQuestDifficultyReleaseRequiredSentence(
            MstStageModel difficultyReleaseTargetMstStage)
        {
            // 開放に必要なクエストの情報
            var difficultyReleaseRequiredMstStage = MstStageDataRepository.GetMstStages()
                .FirstOrDefault(mstStage => mstStage.Id == difficultyReleaseTargetMstStage.ReleaseRequiredMstStageId) ?? MstStageModel.Empty;

            if (!difficultyReleaseRequiredMstStage.IsEmpty())
            {
                var difficultyReleaseRequiredMstQuest =
                    MstQuestDataRepository.GetMstQuestModels().First(mstQuest => mstQuest.Id == difficultyReleaseRequiredMstStage.MstQuestId);
                return QuestDifficultyReleaseRequiredSentence.CreateFormattedSentence(
                    difficultyReleaseRequiredMstQuest.Name,
                    difficultyReleaseRequiredMstQuest.Difficulty,
                    difficultyReleaseRequiredMstStage.StageNumber);
            }
            else
            {
                return QuestDifficultyReleaseRequiredSentence.CreateEmptySentence();
            }
        }
    }
}
