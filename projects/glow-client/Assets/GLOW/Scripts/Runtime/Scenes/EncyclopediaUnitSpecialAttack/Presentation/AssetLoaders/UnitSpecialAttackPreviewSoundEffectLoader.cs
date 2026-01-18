using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Field;
using UnityEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.AssetLoaders
{
    public class UnitSpecialAttackPreviewSoundEffectLoader : IUnitSpecialAttackPreviewSoundEffectLoader
    {
        static readonly IReadOnlyList<SoundEffectId> CommonSoundEffects = new List<SoundEffectId>
        {
            SoundEffectId.SSE_051_063,
            SoundEffectId.SSE_072_012,
            SoundEffectId.SSE_051_045,
            SoundEffectId.SSE_072_022,
        };

        [Inject] ISoundEffectManagement SoundEffectManagement { get; }

        public async UniTask Load(UnitAttackViewInfo attackViewInfo, CancellationToken cancellationToken)
        {
            await SoundEffectManagement.Load(cancellationToken, GetSoundEffectAssetKeys(attackViewInfo));
        }

        public void Unload(UnitAttackViewInfo attackViewInfo)
        {
            SoundEffectManagement.Unload(GetSoundEffectAssetKeys(attackViewInfo));
        }

        string[] GetSoundEffectAssetKeys(UnitAttackViewInfo attackViewInfo)
        {
            var assetKeys = new List<SoundEffectAssetKey>();
            var soundEffectIds = GetSoundEffectIds(attackViewInfo).Distinct();

            foreach (var id in soundEffectIds)
            {
                if (SoundEffects.Dictionary.TryGetValue(id, out var soundEffect))
                {
                    if (soundEffect.Tag == SoundEffectTag.Common) continue; // Commonはロードされてるはずなので除く

                    assetKeys.Add(soundEffect.AssetKey);
                }
            }

            return assetKeys.Select(assetKey => assetKey.Value.ToString()).ToArray();
        }

        List<SoundEffectId> GetSoundEffectIds(UnitAttackViewInfo attackViewInfo)
        {
            var soundEffectIds = CommonSoundEffects.ToList();

            if (attackViewInfo != null)
            {
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackEffect));
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackEffectMirror));
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackLastingEffect));
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackLastingEffectMirror));
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackStayedLastingEffect));
                soundEffectIds.AddRange(GetSoundEffectIdsInBattleEffect(attackViewInfo.AttackStayedLastingEffectMirror));
                soundEffectIds.AddRange(GetSoundEffectIdsInMangaEffect(attackViewInfo.AttackMangaEffect));
                soundEffectIds.AddRange(GetSoundEffectIdsInMangaEffect(attackViewInfo.AttackMangaEffectMirror));
            }

            return soundEffectIds;
        }

        List<SoundEffectId> GetSoundEffectIdsInBattleEffect(GameObject effectGameObject)
        {
            if (effectGameObject == null) return new List<SoundEffectId>();

            var battleEffectView = effectGameObject.GetComponent<BattleEffectView>();
            if (battleEffectView == null) return new List<SoundEffectId>();

            return battleEffectView.GetSoundEffectIds();
        }

        List<SoundEffectId> GetSoundEffectIdsInMangaEffect(GameObject effectGameObject)
        {
            if (effectGameObject == null) return new List<SoundEffectId>();

            var battleEffectView = effectGameObject.GetComponent<TimelineMangaEffectComponent>();
            if (battleEffectView == null) return new List<SoundEffectId>();

            return battleEffectView.GetSoundEffectIds();
        }
    }
}
