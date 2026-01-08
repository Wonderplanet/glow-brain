using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Notice.Domain.Initializer;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using GLOW.Scenes.AnnouncementWindow.Domain.Evaluator;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Notice.Domain.Factory;
using GLOW.Scenes.Notice.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class DisplayAtLoginUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ILoginAnnouncementEvaluator LoginAnnouncementEvaluator { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IDisplayedInGameNoticeRecordResetter DisplayedInGameNoticeRecordResetter { get; }
        [Inject] IDisplayNoticeListFactory DisplayNoticeListFactory { get; }
        [Inject] ITutorialPlayingStatus TutorialPlayingStatus { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] IPvpTutorialPlayingStatus PvpTutorialPlayingStatus { get; }
        
        public DisplayAtLoginUseCaseModel CheckDisplayAtLogin()
        {
            var playingTutorialSequenceFlag = PlayingTutorialSequenceEvaluator.IsPlayingTutorial(
                TutorialPlayingStatus, 
                FreePartTutorialPlayingStatus,
                PvpTutorialPlayingStatus);
            
            // チュートリアルシーケンス中の場合
            if (playingTutorialSequenceFlag)
            {
                return new DisplayAtLoginUseCaseModel(
                    DisplayAtLoginFlag.False,
                    new List<NoticeModel>(),
                    playingTutorialSequenceFlag);
            }
            
            var isFirstLoginAnnouncementShow = LoginAnnouncementEvaluator.ShouldShowLoginAnnouncement(
                AnnouncementCacheRepository.GetInformationLastUpdated(),
                AnnouncementCacheRepository.GetOperationLastUpdated());
            
            // 日が変わっていた場合の初期化
            DisplayedInGameNoticeRecordResetter.ResetDisplayedInGameNoticeRecord();
            
            // IDが保存されていないものから上位3つ
            var inGameNoticeUseCaseModels = DisplayNoticeListFactory.CreateDisplayNoticeList();
            
            // インゲームノーティスの情報をクリアすることで、ホームに遷移する度に再表示されないようにする
            GameManagement.SaveGameFetchOther(ClearOprInGameNoticeModel());

            return new DisplayAtLoginUseCaseModel(
                new DisplayAtLoginFlag(isFirstLoginAnnouncementShow),
                inGameNoticeUseCaseModels,
                playingTutorialSequenceFlag);
        }
        
        GameFetchOtherModel ClearOprInGameNoticeModel()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            
            var newGameFetchOther = fetchOtherModel with
            {
                OprInGameNoticeModels = new List<OprNoticeModel>()
            };

            return newGameFetchOther;
        }
    }
}