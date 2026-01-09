using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;

namespace GLOW.Core.Data.Repositories
{
    public class ResumableStateRepository : IResumableStateRepository
    {
        ResumableStateModel _resumableStateModel;

        ResumableStateModel IResumableStateRepository.Get()
        {
            if(_resumableStateModel == null) return ResumableStateModel.Empty;
            return _resumableStateModel;
        }

        void IResumableStateRepository.Save(ResumableStateModel model)
        {
            //中断復帰 > InGameでは保存してないので一律HomeTOPになってる
            _resumableStateModel = model;
        }

        void IResumableStateRepository.Clear()
        {
            _resumableStateModel = ResumableStateModel.Empty;
        }
    }
}
