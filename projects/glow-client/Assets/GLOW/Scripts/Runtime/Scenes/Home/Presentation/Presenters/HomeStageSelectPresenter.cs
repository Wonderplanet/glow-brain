using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public sealed class HomeStageSelectPresenter : IHomeStageSelectViewDelegate
    {
        [Inject] HomeStageSelectUseCases UseCases { get; }
        [Inject] HomeStageSelectViewController ViewController { get; }

        readonly HomeMainViewModelTranslator _viewModelTranslator = new();

        void IHomeStageSelectViewDelegate.OnViewDidLoad()
        {
            var selectedStageDataModel = UseCases.UpdateAndGetQuestUseCaseModel();
            SetStageDataList(_viewModelTranslator.TranslateToHomeMainQuestViewModel(selectedStageDataModel));
        }

        void IHomeStageSelectViewDelegate.OnStageSelected(HomeMainStageViewModel stageViewModel)
        {
            // NOTE: 選択したステージ情報を保存して画面を閉じる
            // UseCases.SetSelectedMstQuestId(stageViewModel.MstStageId.Value);

            ViewController.Dismiss();
        }

        void SetStageDataList(HomeMainQuestViewModel questViewModel)
        {
            ViewController.SetStageDataList(questViewModel);
        }
    }
}
