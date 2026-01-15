using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkPanelComponent : MonoBehaviour
    {
        [SerializeField] ArtworkFragmentPanelComponent _artworkFragmentPanel;
        [SerializeField] TimelineAnimation _completeTimeline;
        [SerializeField] UIObject _kira02;
        [SerializeField] UIObject _kira01;
        [SerializeField] UIObject _frameEffect;
        [SerializeField] UIObject _completePlate;
        [SerializeField] UIObject _completeHpPlate;
        [SerializeField] UIText _completeHpText;
        [SerializeField] ArtworkFragmentReleaseAnimation[] _artworkFragmentReleaseAnimations;

        public void Setup(ArtworkPanelViewModel model)
        {
            _artworkFragmentPanel.Setup(model.FragmentPanelViewModel);
        }

        public async UniTask PlayArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions, CancellationToken cancellationToken)
        {
            foreach (var position in positions)
            {
                if (position.Value <= 16)
                {
                    _artworkFragmentReleaseAnimations[position.Value - 1].PlayArtworkFragmentAnimation(
                        () => {
                            _artworkFragmentPanel.GetPiece(position.Value - 1).SetCanvasGroupAlpha(0);
                        });
                    await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken: cancellationToken);
                }
            }

            await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: cancellationToken);
        }

        public void SkipArtworkFragmentAnimation(IReadOnlyList<ArtworkFragmentPositionNum> positions)
        {
            foreach (var position in positions)
            {
                if (position.Value <= 16)
                {
                    _artworkFragmentReleaseAnimations[position.Value - 1].Hidden = true;
                    _artworkFragmentPanel.GetPiece(position.Value - 1).gameObject.SetActive(false);
                }
            }
        }

        public async UniTask PlayArtworkCompleteAnimation(HP addHp, CancellationToken cancellationToken)
        {
            _kira02.Hidden = false;
            _kira01.Hidden = false;
            _frameEffect.Hidden = false;

            if (addHp.Value > 0)
            {
                _completeHpText.SetText($"+{addHp.Value}");
                _completeHpPlate.Hidden = false;
            }
            else
            {
                _completePlate.Hidden = false;
            }

            await _completeTimeline.PlayAsync(cancellationToken);
        }

        public void SkipArtworkCompleteAnimation()
        {
            _completeTimeline.Skip();

            _kira02.Hidden = true;
            _kira01.Hidden = true;
            _frameEffect.Hidden = true;
            _completePlate.Hidden = true;
            _completeHpPlate.Hidden = true;
        }
    }
}
