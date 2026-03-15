using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.HomeMainKomaSettingFilter.Presentation;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;
using GLOW.Scenes.HomeMenuSetting.Presentation.Translator;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainKomaSettingUnitSelectPresenter : IHomeMainKomaSettingUnitSelectViewDelegate
    {
        [Inject] HomeMainKomaSettingUnitSelectViewController ViewController { get; }
        [Inject] HomeMainKomaSettingUnitSelectViewController.Argument Argument { get; }
        [Inject] HomeMainKomaSettingUnitSelectUseCase UseCase { get; }
        [Inject] HomeMainKomaSettingWireFrame WireFrame { get; }

        void IHomeMainKomaSettingUnitSelectViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeView();

            SetUpView(Argument.CurrentSettingMstUnitId);
        }

        void IHomeMainKomaSettingUnitSelectViewDelegate.UpdateSelectingUnit(
            MasterDataId mstUnitId)
        {

            if (!IsChangeableUnit(mstUnitId))
            {
                // 変更できない場合は何もしない
                return;
            }
            SetUpView(mstUnitId);
        }

        void IHomeMainKomaSettingUnitSelectViewDelegate.OnFilterButtonTapped(MasterDataId mstUnitId)
        {
            WireFrame.ShowFilterView(
                () => { SetUpView(mstUnitId);},
                () => {  }
                );
        }

        void SetUpView(MasterDataId mstUnitId)
        {
            var model = UseCase.GetModel(
                mstUnitId,
                Argument.OtherSettingMstUnitIds);

            ViewController.SetUpView(HomeMainKomaSettingUnitSelectViewModelTranslator.Translate(model));
        }

        bool IsChangeableUnit(MasterDataId mstUnitId)
        {
            // 他で設定しているユニットIDに含まれていなければ変更可能
            return !Argument.OtherSettingMstUnitIds.Contains(mstUnitId);
        }


    }
}
