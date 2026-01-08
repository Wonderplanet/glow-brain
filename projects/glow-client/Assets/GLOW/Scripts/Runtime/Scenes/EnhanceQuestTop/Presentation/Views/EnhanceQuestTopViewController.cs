using GLOW.Scenes.EnhanceQuestTop.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-2_ 1日N回コイン獲得クエストTOP画面
    /// </summary>
    public class EnhanceQuestTopViewController : UIViewController<EnhanceQuestTopView>
    {
        [Inject] IEnhanceQuestTopViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(EnhanceQuestTopViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void UpdateTopView(UpdatedEnhanceQuestTopViewModel viewModel)
        {
            ActualView.UpdatePartyName(viewModel.PartyName);
            ActualView.UpdateBonus(viewModel.TotalBonusPercentage);
        }

        [UIAction]
        void OnEnhanceQuestButtonTapped()
        {
            ViewDelegate.OnEnhanceQuestButtonTapped();
        }

        [UIAction]
        void OnAdChallengeButtonTapped()
        {
            ViewDelegate.OnAdChallengeButtonTapped();
        }

        [UIAction]
        void OnPartyFormationButtonTapped()
        {
            ViewDelegate.OnPartyFormationButtonTapped();
        }

        [UIAction]
        void OnBonusUnitButtonTapped()
        {
            ViewDelegate.OnBonusUnitButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnInfoButtonTapped()
        {
            ViewDelegate.OnInfoButtonTapped();
        }

        [UIAction]
        void OnHelpButtonTapped()
        {
            ViewDelegate.OnHelpButtonTapped();
        }
    }
}
