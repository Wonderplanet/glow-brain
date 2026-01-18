using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.GachaCostItemDetailView.Domain.UseCases;
using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Translator;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using Zenject;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.Presenters
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-6_ガシャチケット詳細画面
    /// </summary>
    public class GachaCostItemDetailPresenter : IGachaCostItemDetailViewDelegate
    {
        [Inject] GachaCostItemDetailViewController ViewController { get; }
        [Inject] GachaCostItemDetailUseCase GachaCostItemDetailUseCase { get; }
        [Inject] GachaCostItemDetailViewController.Argument Argument { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        
        TransitionButtonGrayOutFlag _transitionButtonGrayOutFlag;
        
        public void OnViewDidLoad()
        {
            var useCaseModel = GachaCostItemDetailUseCase.GetUseCaseModelById(Argument.MstCostId);
            var viewModel = GachaCostItemDetailTranslator.Translate(useCaseModel, Argument.ShowTransitAreaFlag);
            _transitionButtonGrayOutFlag = useCaseModel.TransitionButtonGrayOutFlag;
            ViewController.SetViewModel(viewModel);
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        public void OnTransitionButtonTapped()
        {
            if (!Argument.ShowTransitAreaFlag.Value || _transitionButtonGrayOutFlag)
            {
                // 開催期間外表示をする
                CommonToastWireFrame.ShowScreenCenterToast("ガシャ開催期間外です");
                return;
            }
            
            HomeViewControl.OnGachaSelected();
            ViewController.Dismiss();
        }
    }
}