using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public sealed class DebugAssetExistsCheckerUseCase
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAssetSource AssetSource { get; }

        public string GetAssetExistsCheckText()
        {
           var result ="";
           result += GetCharacterAssetChecks();
           result += GetEnemyAssetChecks();
           result += GetEventAssetChecks();
           result += GetItemAssetChecks();
           result += GetEmblemAssetChecks();

            return result;
        }


        string GetCharacterAssetChecks()
        {
            // やってないやつ。実装されたらAssetKeyからPath作成してチェックする
            // gacha top cutin of 3kind

            var result = "==== UNIT ====\n";
            foreach (var a in MstCharacterDataRepository.GetCharacters())
            {
                // unit icon
                if (!AssetSource.IsAddressExists(CharacterIconAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += CharacterIconAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
                // unit sp icon
                if (!AssetSource.IsAddressExists(CharacterSpecialAttackIconAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += CharacterSpecialAttackIconAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
                // unit long icon
                if (!AssetSource.IsAddressExists(CharacterIconAssetPath.FromAssetKeyForLSize(a.AssetKey).Value))
                    result += CharacterIconAssetPath.FromAssetKeyForLSize(a.AssetKey).Value + " \n";
                // unit long sp icon
                if (!AssetSource.IsAddressExists(CharacterSpecialAttackIconAssetPath.FromAssetKeyForLSize(a.AssetKey)
                        .Value))
                    result += CharacterSpecialAttackIconAssetPath.FromAssetKeyForLSize(a.AssetKey).Value + " \n";
                // unit stand image
                if (!AssetSource.IsAddressExists(CharacterStandImageAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += CharacterStandImageAssetPath.FromAssetKey(a.AssetKey).Value + " \n";

                // ==SPINE==
                if (!AssetSource.IsAddressExists(UnitImageAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += UnitImageAssetPath.FromAssetKey(a.AssetKey).Value + " \n";

                // ==INGAME==
                // attack view info
                if (!AssetSource.IsAddressExists(UnitAttackViewInfoSetAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += UnitAttackViewInfoSetAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
            }

            return result;
        }
        string GetEnemyAssetChecks()
        {
            var result = "==== ENEMY ====\n";
            foreach (var a in MstEnemyCharacterDataRepository.GetEnemyCharacters())
            {
                // unit enemy icon s
                if (!AssetSource.IsAddressExists(EnemyCharacterSmallIconAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += EnemyCharacterSmallIconAssetPath.FromAssetKey(a.AssetKey).Value + " \n";

                // ==SPINE==
                if (!AssetSource.IsAddressExists(UnitImageAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += UnitImageAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
                
                // ==INGAME==
                // attack view info
                if (!AssetSource.IsAddressExists(UnitAttackViewInfoSetAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += UnitAttackViewInfoSetAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
            }
            return result;
        }
        string GetEventAssetChecks()
        {
            var result = "==== EVENT ====\n";
            var eventQuests = MstQuestDataRepository.GetMstQuestModels()
                    .Where(m => m.QuestType == QuestType.Event)
                    .Where(m => m.StartDate <= TimeProvider.Now); //これそのうちEndAtも見ないと全部出ることになる

            foreach (var a in eventQuests)
            {
                // event unitstandimage
                if(!AssetSource.IsAddressExists(a.AssetKey.ToEventAddressablePath()))
                    result += a.AssetKey.ToEventAddressablePath() + " \n";
            }
            return result;
        }
        string GetItemAssetChecks()
        {
            var result = "==== ITEM ====\n";
            foreach (var a in MstItemDataRepository.GetItems())
            {
                // item icon peace
                if (!AssetSource.IsAddressExists(ItemIconAssetPath.FromAssetKey(a.ItemAssetKey).Value))
                    result += ItemIconAssetPath.FromAssetKey(a.ItemAssetKey).Value + " \n";
            }
            return result;
        }
        string GetEmblemAssetChecks()
        {
            var result = "==== EMBLEM ====\n";
            foreach (var a in MstEmblemRepository.GetMstEmblems())
            {
                // emblem icon
                if (!AssetSource.IsAddressExists(EmblemIconAssetPath.FromAssetKey(a.AssetKey).Value))
                    result += EmblemIconAssetPath.FromAssetKey(a.AssetKey).Value + " \n";
            }
            return result;
        }
    }
}
