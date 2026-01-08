using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class StartRushLayer : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] List<UIImage> _unitImages;
        
        [SerializeField] UIObject _level1StartRushTitleComponent;
        [SerializeField] UIObject _level2StartRushTitleComponent;
        [SerializeField] UIObject _level3StartRushTitleComponent;
        [SerializeField] UIObject _level1StartRushEffectComponent;
        [SerializeField] UIObject _level2StartRushEffectComponent;
        [SerializeField] UIObject _level3StartRushEffectComponent;

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            gameObject.SetActive(true);
            if (_timelineAnimation != null)
            {
                await _timelineAnimation.PlayAsync(cancellationToken);
            }
            gameObject.SetActive(false);
        }

        public void SetupRushUnitImage(IReadOnlyList<UnitAssetKey> unitAssetKeys)
        {
            for (var i = 0; i < unitAssetKeys.Count; i++)
            {
                var unitAssetKey = unitAssetKeys[i];

                if (i >= _unitImages.Count) return;
                var unitImage = _unitImages[i];

                var rushImage = RushUnitImageAssetPath.FromAssetKey(unitAssetKey);
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(unitImage.Image, rushImage.Value);
                unitImage.Hidden = false;
            }
        }
        
        public void SetupRushLevel(RushChargeCount chargeCount)
        {
            _level1StartRushTitleComponent.IsVisible = chargeCount.Value == 1;
            _level1StartRushEffectComponent.IsVisible = chargeCount.Value == 1;
            _level2StartRushTitleComponent.IsVisible = chargeCount.Value == 2;
            _level2StartRushEffectComponent.IsVisible = chargeCount.Value == 2;
            _level3StartRushTitleComponent.IsVisible = chargeCount.Value >= 3;
            _level3StartRushEffectComponent.IsVisible = chargeCount.Value >= 3;
        }

        public void Pause(bool pause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(pause);
            }
        }

        public void Skip()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Skip();
            }
        }

        public void HiddenUnitImage()
        {
            foreach (var image in _unitImages)
            {
                image.Hidden = true;
            }
        }
    }
}
