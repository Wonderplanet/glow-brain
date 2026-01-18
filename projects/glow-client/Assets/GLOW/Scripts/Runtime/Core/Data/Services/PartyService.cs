using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class PartyService : IPartyService
    {
        [Inject] PartyApi PartyApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        public async UniTask<PartySaveResultModel> Save(CancellationToken cancellationToken, PartyNo no, PartyName name, IReadOnlyList<UserDataId> partyUnitIds)
        {
            try
            {
                var requestData = TranslateToRequestData(no, name, partyUnitIds);
                var partySaveResultData = await PartyApi.Save(cancellationToken, new[] { requestData});
                return TranslateToResultModel(partySaveResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
        
        public async UniTask<PartySaveResultModel> Save(CancellationToken cancellationToken, List<UserPartyModel> userPartyModels)
        {
            try
            {
                var parties = userPartyModels
                    .Select(model => TranslateToRequestData(model.PartyNo, model.PartyName, model.GetUnitList()))
                    .ToArray();
                var partySaveResultData = await PartyApi.Save(cancellationToken, parties);
                return TranslateToResultModel(partySaveResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        PartySaveRequestData TranslateToRequestData(PartyNo no, PartyName name, IReadOnlyList<UserDataId> partyUnitIds)
        {
            return new PartySaveRequestData()
            {
                PartyNo = no.Value,
                PartyName = name.Value,
                Units = partyUnitIds
                    .Where(id => !id.IsEmpty())
                    .Select(id => id.ToString())
                    .ToArray()

            };
        }

        PartySaveResultModel TranslateToResultModel(PartySaveResultData data)
        {
            var parties = data.UsrParties
                .Select(UserPartyDataTranslator.TranslateToModel)
                .ToList();
            return new PartySaveResultModel(parties);
        }
    }
}
