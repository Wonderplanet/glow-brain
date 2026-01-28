using GLOW.Scenes.SpecialAttackInfo.Domain.UseCases;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Translator;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Presenters
{
    public class SpecialAttackInfoPresenter : ISpecialAttackInfoViewDelegate
    {
        [Inject] SpecialAttackInfoViewController ViewController { get; }
        [Inject] SpecialAttackInfoViewController.Argument Argument { get; }
        [Inject] GetSpecialAttackInfoModelUseCase UseCase { get; }

        public void OnViewDidLoad()
        {
            var infoModel = UseCase.GetSpecialAttackInfoModel(Argument.UnitId, Argument.UnitGrade, Argument.UnitLevel);
            var viewModel = SpecialAttackInfoViewModelTranslator.ToSpecialAttackInfoModel(infoModel);

            SetInfoRankModelList(viewModel);
            SetInfo(viewModel);
        }

        void SetInfo(SpecialAttackInfoViewModel viewModel)
        {
            ViewController.SetInfo(viewModel);
        }

        void SetInfoRankModelList(SpecialAttackInfoViewModel viewModel)
        {
            ViewController.SetInfoRankModelList(viewModel);
        }

        public void OnClose()
        {
            // 画面を閉じる
            ViewController.Dismiss();
        }
    }
}
