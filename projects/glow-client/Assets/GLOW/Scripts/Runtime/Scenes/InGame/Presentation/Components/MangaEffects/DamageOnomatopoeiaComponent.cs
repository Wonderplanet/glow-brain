using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DamageOnomatopoeiaComponent : AbstractMangaEffectComponent
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class DamageOnomatopoeiaInfo
        {
            public string AssetKey;
            public CharacterColor Color;
            public AnimationMangaEffectElement OnomatopoeiaPrefab;
        }

        [SerializeField] List<DamageOnomatopoeiaInfo> _damageOnomatopoeiaInfoList;

        AnimationMangaEffectElement _mangaEffectElement;

        public DamageOnomatopoeiaComponent Setup(AttackHitOnomatopoeiaAssetKey onomatopoeia, CharacterColor unitColor, bool isKiller)
        {
            CharacterColor color = isKiller ? unitColor : CharacterColor.None;
            var onomatopoeiaInfo =
                _damageOnomatopoeiaInfoList.FirstOrDefault(info => info.AssetKey == onomatopoeia.Value && info.Color == color);

            if (onomatopoeiaInfo == null) return null;

            _mangaEffectElement = InstantiateMangaEffectElement(onomatopoeiaInfo.OnomatopoeiaPrefab, transform);

            return this;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            OnCompleted?.Invoke();
        }

        public override void Destroy()
        {
            Destroy(gameObject);
        }

        public override AbstractMangaEffectComponent Play()
        {
            if (_mangaEffectElement == null) return this;

            _mangaEffectElement.AnimationPlayer.OnDone = OnAnimationCompleted;
            _mangaEffectElement.AnimationPlayer.Play();

            return this;
        }

        protected override void OnPause(bool pause)
        {
            _mangaEffectElement.AnimationPlayer.Pause(pause);
        }

        void OnAnimationCompleted()
        {
            Destroy(gameObject);
        }
    }
}
