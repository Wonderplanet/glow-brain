using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    [Serializable]
    public class HomeMainKomaPatternButton
    {
        public Button button;
        public UIImage unitImage;
        public GameObject plusIconObject;
        public int index;
    }
    public class HomeMainKomaPatternComponent : MonoBehaviour
    {
        [SerializeField] Animator _animator;
        [SerializeField] HomeMainKomaPatternButton[] _unitButtons;

        public HomeMainKomaPatternButton[] UnitButtons => _unitButtons;

        // animationName
        const string ShowAnimationName = "home_main_koma_pattern_in";

        public void InitializeView()
        {
            foreach (var unitButton in _unitButtons)
            {
                unitButton.button.interactable = false;
                unitButton.unitImage.gameObject.SetActive(false);
                unitButton.plusIconObject.SetActive(false);
            }
        }

        public void Setup(IReadOnlyList<HomeMainKomaUnitViewModel> viewModels, bool isShowEmptyIcon)
        {
            // indexないものはempty, あるものは表示処理する
            for(var i =0;_unitButtons.Length > i; i++)
            {
                var vm = viewModels.FirstOrDefault(x => x.PlaceIndex.Value == i);
                if (vm == null)
                {
                    // empty設定
                    _unitButtons[i].unitImage.gameObject.SetActive(false);
                    _unitButtons[i].plusIconObject.SetActive(isShowEmptyIcon);
                    continue;
                }

                // 表示設定
                var unitButton = _unitButtons[i];
                unitButton.unitImage.gameObject.SetActive(true);
                unitButton.plusIconObject.SetActive(false);

                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                    unitButton.unitImage.Image,
                    vm.HomeMainKomaUnitAssetPath.Value);
            }
        }

        public void EnableEditButton(bool enable)
        {
            foreach (var unitButton in _unitButtons)
            {
                unitButton.button.interactable = enable;
            }
        }

        public void PlayShowAnimation()
        {
            if(_animator == null) return;

            _animator.Play(ShowAnimationName, 0, 0f);
        }
    }

}
