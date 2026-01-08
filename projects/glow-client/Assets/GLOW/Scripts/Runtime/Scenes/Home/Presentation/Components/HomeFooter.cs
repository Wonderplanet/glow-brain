using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Models;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public sealed class HomeFooter : MonoBehaviour
    {
        [Header("ショップ")]
        [SerializeField] GameObject _shopBadge;
        [SerializeField] Animator _shopAnimator;
        [Header("コンテンツ")]
        [SerializeField] GameObject _contentBadge;
        [SerializeField] Animator _contentAnimator;
        [SerializeField] HomeFooterSpeechBalloonSwitchComponent _speechBalloonSwitchComponent;
        [Header("ホーム")]
        [SerializeField] GameObject _homeBadge;
        [SerializeField] Animator _homeAnimator;
        [Header("キャラクター")]
        [SerializeField] GameObject _characterBadge;
        [SerializeField] Animator _characterAnimator;
        [Header("ガシャ")]
        [SerializeField] GameObject _gachaBadge;
        [SerializeField] Animator _gachaAnimator;
        [SerializeField] GameObject _gachaBalloon;
        [Header("アニメーション名")]
        [SerializeField] string _focus = "focus";
        [SerializeField] string _unfocus = "unfocus";

        public bool ShopBadge
        {
            set => _shopBadge.SetActive(value);
        }
        public bool ContentBadge
        {
            set => _contentBadge.SetActive(value);
        }
        public bool HomeBadge
        {
            set => _homeBadge.SetActive(value);
        }
        public bool CharacterBadge
        {
            set => _characterBadge.SetActive(value);
        }
        public bool GachaBadge
        {
            set => _gachaBadge.SetActive(value);
        }

        HomeContentTypes _beforeType = HomeContentTypes.Main;


        public void SetActiveContent(HomeContentTypes contentType)
        {
            Animate(_beforeType, contentType);
            _beforeType = contentType;
        }

        public void SetBalloons(
            HomeFooterBalloonShownFlag shouldShowGacha,
            HomeFooterBalloonShownFlag shouldShowAdventBattle,
            HomeFooterBalloonShownFlag shouldShowPvp)
        {
            _gachaBalloon.SetActive(shouldShowGacha);
            _speechBalloonSwitchComponent.SetUpSpeechBalloons(
                shouldShowAdventBattle,
                shouldShowPvp);
        }

        void Animate(HomeContentTypes beforeContentType, HomeContentTypes contentType)
        {
            switch (beforeContentType)
            {
                case HomeContentTypes.Main:
                    _homeAnimator.SetTrigger(_unfocus);
                    break;
                case HomeContentTypes.Character:
                    _characterAnimator.SetTrigger(_unfocus);
                    break;
                case HomeContentTypes.Gacha:
                    _gachaAnimator.SetTrigger(_unfocus);
                    break;
                case HomeContentTypes.Shop:
                    _shopAnimator.SetTrigger(_unfocus);
                    break;
                case HomeContentTypes.Content:
                    _contentAnimator.SetTrigger(_unfocus);
                    break;
            }
            switch (contentType)
            {
                case HomeContentTypes.Main:
                    _homeAnimator.SetTrigger(_focus);
                    break;
                case HomeContentTypes.Character:
                    _characterAnimator.SetTrigger(_focus);
                    break;
                case HomeContentTypes.Gacha:
                    _gachaAnimator.SetTrigger(_focus);
                    break;
                case HomeContentTypes.Shop:
                    _shopAnimator.SetTrigger(_focus);
                    break;
                case HomeContentTypes.Content:
                    _contentAnimator.SetTrigger(_focus);
                    break;
            }
        }

    }
}
