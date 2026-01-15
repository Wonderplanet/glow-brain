using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.Model;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.BoxGachaLineupDialog.Domain.UseCase
{
    public class ShowBoxGachaLineupUseCase
    {
        [Inject] IMstBoxGachaDataRepository MstBoxGachaDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        
        public BoxGachaLineupModel GetLineup(MasterDataId mstBoxGachaId, BoxLevel currentBoxLevel)
        {
            var mstBoxGachaGroupModel = MstBoxGachaDataRepository.GetMstBoxGachaGroupModelFirstOrDefault(
                mstBoxGachaId,
                currentBoxLevel);
            
            var cells = CreateCells(mstBoxGachaGroupModel.Prizes);
            var isUnitContainInLineup = mstBoxGachaGroupModel.Prizes
                .Any(prize => prize.ResourceType == ResourceType.Unit);
            
            return new BoxGachaLineupModel(
                currentBoxLevel,
                CreateLineupListByRarity(Rarity.UR, cells),
                CreateLineupListByRarity(Rarity.SSR, cells),
                CreateLineupListByRarity(Rarity.SR, cells),
                CreateLineupListByRarity(Rarity.R, cells),
                new UnitContainInLineupFlag(isUnitContainInLineup));
        }
        
        IReadOnlyList<BoxGachaLineupCellModel> CreateCells(IReadOnlyList<MstBoxGachaPrizeModel> prizes)
        {
            return prizes.Select(prize => new BoxGachaLineupCellModel(
                PlayerResourceModelFactory.Create(
                    prize.ResourceType,
                    prize.ResourceId,
                    prize.ResourceAmount.ToPlayerResourceAmount()),
                prize.Stock)).ToList();
        }
        
        BoxGachaLineupListModel CreateLineupListByRarity(
            Rarity rarity,
            IReadOnlyList<BoxGachaLineupCellModel> cells)
        {
            var filteredCells = cells
                .Where(cell => cell.PrizeIconModel.Rarity == rarity)
                .ToList();

            return new BoxGachaLineupListModel(rarity, filteredCells);
        }
    }
}