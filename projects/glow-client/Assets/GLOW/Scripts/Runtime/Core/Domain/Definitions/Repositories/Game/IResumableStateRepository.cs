using GLOW.Core.Domain.UseCases;

namespace GLOW.Core.Domain.Repositories
{
    // インゲームから戻ってきたときに、前回の状態を復元するためのリポジトリ
    public interface IResumableStateRepository
    {
        ResumableStateModel Get();
        void Save(ResumableStateModel model);
        void Clear();
    }
}
