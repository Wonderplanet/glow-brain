using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using UnityEngine;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public class MainPart3TutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // ホーム画面
            await FadeInGrayOut(cancellationToken);
            
            // カルーセルのスクロールを無効にする
            var questView = FindTargetObject("home_main_quest_view").GetComponent<HomeMainQuestView>();
            questView.CarouselView.enabled = false;
            
            
            await ShowTutorialText(cancellationToken, "挑戦する前にステージの\n詳細情報を確認してみよう！", 0f);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("home_stage_cell", t => t.GetComponent<HomeMainStageSelectCell>().StageNumber == 1);
            await WaitClickEvent(cancellationToken, "home_stage_button");
            HideIndicator();
            // ダイアログ下のグレーアウトを解除し、オーバーレイのグレーアウトにする
            HideGrayOut();
            ShowOverlayGrayOut();
            
            // カルーセルのスクロールを有効にする
            questView.CarouselView.enabled = true;
            
            // ダイアログ表示まで待つ
            await WaitViewPresentation<HomeStageInfoViewController>(cancellationToken);
            
            // カルーセルのセルに表示不具合が起こるので表示しなおす
            var cellObject = FindTargetObject(
                "home_stage_cell", 
                t => t.GetComponent<HomeMainStageSelectCell>().StageNumber == 1).gameObject;
            cellObject.SetActive(false);
            // カルーセルビューの処理で再表示されるが安全のため1F待って再表示
            await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
            cellObject.SetActive(true);
            
            // ステージ情報のハイライト
            var infoHeight = FindTargetObject("stage_detail_info").GetRectTransform().rect.height;
            var backAreaRect = FindTargetObject("stage_detail_info_back").GetRectTransform();
            backAreaRect.SetSizeWithCurrentAnchors(
                RectTransform.Axis.Vertical,
                infoHeight
            );
            
            HighlightTarget("stage_detail_info_back");
            HighlightTarget("stage_detail_info");
            
            await ShowTutorialText(cancellationToken, "ステージの詳細情報では\n出現ファントムの情報が分かるよ！", -200f);
            await ShowTutorialText(cancellationToken, "クリアが難しくなってきたら\n情報を見て有利なキャラを編成してね！", -200f);
            await HideTutorialText(cancellationToken);
            UnHighlightTarget();
            backAreaRect.SetSizeWithCurrentAnchors(
                RectTransform.Axis.Vertical,
                0f
            );
            
            ShowIndicator("close_stage_detail_button");
            await WaitDismissModal<HomeStageInfoViewController>(cancellationToken);
            HideIndicator();
            
            
            // チュートリアル進捗更新
            await ProgressTutorialStatus(cancellationToken);
            
            // メインパート3終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}
