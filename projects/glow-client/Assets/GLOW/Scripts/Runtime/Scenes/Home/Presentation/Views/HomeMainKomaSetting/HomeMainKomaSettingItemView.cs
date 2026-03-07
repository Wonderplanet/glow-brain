using System;
using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public class HomeMainKomaSettingItemView : UIView
    {
        [SerializeField] RectTransform _contentArea;

        HomeMainKomaPatternComponent _instanced;
        public void InitializeView(
            GameObject patternAsset,
            Action<HomeMainKomaUnitAssetSetPlaceIndex> onUnitEditButtonTapped)
        {
            if(_instanced) return;

            // コマ生成
            _instanced = Instantiate(patternAsset, _contentArea)
                .GetComponent<HomeMainKomaPatternComponent>();
            _instanced.InitializeView();

            // ボタン登録
            foreach (var unitButton in _instanced.UnitButtons)
            {
                unitButton.button.onClick.AddListener(() =>
                {
                    onUnitEditButtonTapped?
                        .Invoke(new HomeMainKomaUnitAssetSetPlaceIndex(unitButton.index));
                });
            }
            // 編集可能にボタン適用
            _instanced.EnableEditButton(true);
        }

        public void SetUpView(IReadOnlyList<HomeMainKomaUnitViewModel> homeMainKomaUnitViewModels)
        {
            _instanced.Setup(homeMainKomaUnitViewModels, true);
        }
    }
}
