using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaAnimIconCellComponent : UIObject
    {
        static readonly int Rare = Animator.StringToHash("Rare");
        static readonly int ItemRarity = 0;
        
        [SerializeField] Animator _animator;
        
        int _rarityInt;
        Action<SoundEffectId> _seAction;

        public void Setup(GachaAnimIconInfo viewModel, Action<SoundEffectId> action)
        {
            _rarityInt = (int)viewModel.Rarity + 1;
            _seAction = action;
            if(viewModel.ResourceType != ResourceType.Unit)
            {
                _rarityInt = ItemRarity;
            }
        }

        protected override void OnEnable()
        {
            _animator.SetInteger(Rare, _rarityInt);
        }

        void PlaySoundEffectIfSSRorUR(SoundEffectId id)
        {
            if(Rarity.SSR <= (Rarity)_rarityInt)
            {
                _seAction?.Invoke(id);
            }
        }
    }
}
