using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.EventBonusUnitList.Domain.Models;
using GLOW.Scenes.EventBonusUnitList.Domain.UseCases;
using GLOW.Scenes.EventBonusUnitList.Presentation.ViewModels;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.Presenters
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-7-1_ ボーナスキャラ簡易表示
    /// 　　45-1-7-2_ ボーナスキャラ一覧ダイアログ
    /// </summary>
    public class EventBonusUnitListUnitListPresenter : IEventBonusUnitListViewDelegate
    {
        [Inject] EventBonusUnitListViewController UnitListViewController { get; }
        [Inject] EventBonusUnitListViewController.Argument Argument { get; }
        [Inject] ShowEventBonusUnitListUseCase ShowEventBonusUnitListUseCase { get; }

        void IEventBonusUnitListViewDelegate.OnViewDidLoad()
        {
            var model = ShowEventBonusUnitListUseCase.GetBonus(Argument.EventBonusGroupId, Argument.MstQuestId);
            var viewModel = Translate(model);
            UnitListViewController.Setup(viewModel);
        }

        void IEventBonusUnitListViewDelegate.OnBackButtonTapped()
        {
            UnitListViewController.Dismiss();
        }

        EventBonusUnitListViewModel Translate(EventBonusUnitListModel unitListModel)
        {
            var bonusList = unitListModel.BonusUnits
                .Select(Translate)
                .ToList();

            return new EventBonusUnitListViewModel(bonusList);
        }

        EventBonusUnitViewModel Translate(EventBonusUnitModel model)
        {
            return new EventBonusUnitViewModel(
                CharacterIconViewModelTranslator.Translate(model.Icon),
                model.BonusValue);
        }
    }
}
