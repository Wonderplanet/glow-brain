using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class AnimationMangaEffectElement : MangaEffectElement
    {
        [SerializeField] AnimationPlayer _animationPlayer;

        public AnimationPlayer AnimationPlayer => _animationPlayer;
    }
}
