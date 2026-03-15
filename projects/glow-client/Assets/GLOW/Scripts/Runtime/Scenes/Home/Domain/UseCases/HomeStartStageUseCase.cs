using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Stage;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.TimeMeasurement;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeStartStageUseCase
    {
        [Inject] IStageService StageService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IMstStageDataRepository StageDataRepository { get; }
        [Inject] IMstQuestDataRepository QuestDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IInGameLoadingMeasurement InGameLoadingMeasurement { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] ITutorialStatusApplier TutorialStatusApplier { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public async UniTask StartStage(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            StaminaBoostCount staminaBoostCount,
            bool isChallengeAd = false)
        {
            // 決闘終了時、期間外でエラーが起きると正しくPvpの終了処理が行われないので、決闘の相手情報の破棄を最初に明示的に行う
            PvpSelectedOpponentStatusCacheRepository.ClearOpponentStatus();

            // 現在時間のハッシュコードをランダムシードとして使用する
            var randomSeed = new InGameRandomSeed(TimeProvider.Now.GetHashCode());

            // NOTE: ログの計測開始
            InGameLoadingMeasurement.Start();

            // イベント実装時にisChallengeAdの処理をいれる
            var model = await StageService.Start(
                cancellationToken,
                mstStageId,
                PartyCacheRepository.GetCurrentPartyModel().PartyNo,
                isChallengeAd,
                staminaBoostCount,
                randomSeed);

            UpdateGameFetchAndOtherModel(model);

            PreferenceRepository.SetLastPlayedMstStageId(mstStageId);

            ResumableStateRepository.Save(GetResumableStateModel(mstStageId));
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;
        }

        ResumableStateModel GetResumableStateModel(MasterDataId mstStageId)
        {
            //ここでやるか検討。inject増えがち。
            var mstStageModel = StageDataRepository.GetMstStage(mstStageId);
            var mstQuestModel = QuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);

            // EventだけMstQuestModel.GroupIdを使う
            return mstQuestModel.QuestType switch
            {
                QuestType.Normal => new ResumableStateModel(SceneViewContentCategory.MainStage, mstStageId, MasterDataId.Empty),
                QuestType.Event => new ResumableStateModel(SceneViewContentCategory.EventStage, mstQuestModel.GroupId,mstQuestModel.MstEventId),
                QuestType.Enhance => new ResumableStateModel(SceneViewContentCategory.EnhanceStage, mstStageId, MasterDataId.Empty),
                _ => new ResumableStateModel(SceneViewContentCategory.None, mstStageId, MasterDataId.Empty)
            };
        }

        void UpdateGameFetchAndOtherModel(StageStartResultModel resultModel)
        {
            var defaultModel = GameRepository.GetGameFetch();
            var newFetchModel = defaultModel with
            {
                UserParameterModel = resultModel.UserParameterModel
            };

            var defaultOtherModel = GameRepository.GetGameFetchOther();
            var newOtherModel = defaultOtherModel with
            {
                UserInGameStatusModel = resultModel.UserInGameStatusModel
            };

            GameManagement.SaveGameUpdateAndFetch(newFetchModel, newOtherModel);
        }
    }
}
