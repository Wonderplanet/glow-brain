using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.UnitReceive.Domain.Model;
using Zenject;

namespace GLOW.Scenes.UnitReceive.Domain.UseCase
{
    public class DisplayReceivedUnitUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }
        
        public UnitReceiveModel GetReceivedUnitInfo(MasterDataId mstUnitId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            var displayedUnitIds = AcquisitionDisplayedUnitIdsRepository.GetAcquisitionDisplayedUnitIds();

            // 初回獲得キャラのみ表示するため、既に獲得しているキャラの場合はEmptyを返す
            if (displayedUnitIds.Contains(mstUnitId)) return UnitReceiveModel.Empty;
            
            // 保存処理
            var updatedDisplayedUnitIds = displayedUnitIds.Append(mstUnitId).ToList();
            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(updatedDisplayedUnitIds);
            
            var speechBalloonText = mstUnit.SpeechBalloons
                .FirstOrDefault(
                    model => model.ConditionType == SpeechBalloonConditionType.Summon, 
                    SpeechBalloonModel.Empty)
                .SpeechBalloonText;

            return new UnitReceiveModel(
                mstUnit.Name,
                mstUnit.RoleType,
                mstUnit.Color,
                mstUnit.Rarity,
                UnitCutInKomaAssetPath.FromAssetKey(mstUnit.AssetKey),
                UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey), 
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                speechBalloonText);
        }
    }
}